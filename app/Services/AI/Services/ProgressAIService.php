<?php

namespace App\Services\AI\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Personal Progress AI helper.
 *
 * "Progress" here is the human-facing summary of what the user has actually
 * been doing: completed goal-progress updates, recently completed activities and
 * goal completion. Facts come from SQL; the LLM only frames them.
 */
class ProgressAIService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(User $user, int $windowDays = 30): array
    {
        $since = Carbon::today()->subDays($windowDays - 1);

        // Dated progress updates across all of the user's goals.
        $updates = \App\Models\GoalProgressUpdate::query()
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $since->toDateString())
            ->with('goal:id,title')
            ->orderByDesc('date')
            ->limit((int) config('ai.retrieval.structured_row_limit', 50))
            ->get()
            ->map(fn($u) => [
                'goal' => $u->goal->title ?? '(deleted goal)',
                'date' => optional($u->date)->toDateString(),
                'description' => $u->description,
                'progress_value' => (float) $u->progress_value,
                'time_spent_minutes' => (int) $u->time_spent_minutes,
            ])
            ->all();

        // Categories the user has been active in.
        $recentActivityCategories = $user->dailyActivities()
            ->whereDate('activity_date', '>=', $since->toDateString())
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($row) => ['category' => $row->category, 'activities' => (int) $row->total])
            ->all();

        return [
            'window_days' => $windowDays,
            'recent_progress_updates' => $updates,
            'recent_progress_count' => count($updates),
            'goals_completed_recently' => $user->goals()
                ->where('status', 'completed')
                ->whereDate('completed_at', '>=', $since->toDateString())
                ->count(),
            'active_categories' => $recentActivityCategories,
        ];
    }
}
