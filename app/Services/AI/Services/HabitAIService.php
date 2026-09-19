<?php

namespace App\Services\AI\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Habit AI helper.
 *
 * Consistency figures are derived from real HabitCompletion rows over a window;
 * streaks are NEVER fabricated. When there is not enough history the returned
 * arrays are simply empty and the prompt tells the model to say so.
 */
class HabitAIService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(User $user, int $windowDays = 30): array
    {
        $start = Carbon::today()->subDays($windowDays - 1);

        $habits = $user->habits()->orderBy('id')->get();

        $rows = [];

        foreach ($habits as $habit) {
            $completions = $habit->completions()
                ->whereDate('completed_date', '>=', $start->toDateString())
                ->get();

            $completedDays = $completions
                ->map(fn($c) => optional($c->completed_date)->toDateString())
                ->filter()
                ->unique()
                ->count();

            // Consistency = completed days / days in window (only counting days
            // since the habit actually started).
            $trackedSince = $habit->start_date && $habit->start_date->greaterThan($start)
                ? $habit->start_date->copy()->startOfDay()
                : $start->copy();

            $window = max(1, (int) $trackedSince->diffInDays(Carbon::today()) + 1);

            $rows[] = [
                'title' => $habit->title,
                'status' => $habit->status,
                'frequency' => $habit->frequency,
                'completed_days' => $completedDays,
                'window_days' => $window,
                'consistency_percent' => (int) round(($completedDays / $window) * 100),
                'last_completed' => optional($completions->sortByDesc('completed_date')->first()?->completed_date)->toDateString(),
            ];
        }

        // Sort by consistency so the "most consistent" answer is data-driven.
        usort($rows, fn($a, $b) => $b['consistency_percent'] <=> $a['consistency_percent']);

        return [
            'window_days' => $windowDays,
            'active_habits' => $habits->where('status', 'active')->count(),
            'total_habits' => $habits->count(),
            'habits' => $rows,
            'most_consistent' => array_slice($rows, 0, 3),
            'least_consistent' => array_slice(array_reverse($rows), 0, 3),
        ];
    }
}
