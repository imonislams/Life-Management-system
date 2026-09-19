<?php

namespace App\Services\AI\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Daily Activity AI helper.
 *
 * Produces EXACT structured facts (counts, totals, per-category duration) from
 * the user's own daily activities. The LLM never invents activities: it only
 * summarises what these queries return.
 */
class ActivityAIService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(User $user, string $range = 'today'): array
    {
        [$start, $end] = $this->resolveRange($range);

        $activities = $user->dailyActivities()
            ->with('goal:id,title')
            ->whereBetween('activity_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->limit((int) config('ai.retrieval.structured_row_limit', 50))
            ->get();

        $totalMinutes = 0;
        $byCategory = [];
        $byStatus = [];
        $goalLinked = [];
        $rows = [];

        foreach ($activities as $activity) {
            $minutes = (int) ($activity->resolveDuration() ?? 0);
            $totalMinutes += $minutes;

            $category = $activity->category ?: 'uncategorised';
            $byCategory[$category] = ($byCategory[$category] ?? 0) + $minutes;

            $status = $activity->status ?: 'unknown';
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            if ($activity->goal) {
                $goalLinked[$activity->goal->title] = ($goalLinked[$activity->goal->title] ?? 0) + 1;
            }

            $rows[] = [
                'title' => $activity->title,
                'date' => optional($activity->activity_date)->toDateString(),
                'category' => $activity->category,
                'status' => $activity->status,
                'duration_minutes' => $minutes,
                'goal' => $activity->goal->title ?? null,
                'description' => $activity->description,
            ];
        }

        arsort($byCategory);

        return [
            'range' => $range,
            'range_label' => $this->rangeLabel($range),
            'count' => $activities->count(),
            'total_minutes' => $totalMinutes,
            'top_categories' => array_slice($byCategory, 0, 5, true),
            'by_status' => $byStatus,
            'goal_linked' => $goalLinked,
            'activities' => $rows,
        ];
    }

    public function rangeLabel(string $range): string
    {
        return match ($range) {
            'today' => 'today',
            'yesterday' => 'yesterday',
            'this_week' => 'this week',
            'last_week' => 'last week',
            'this_month' => 'this month',
            'last_month' => 'last month',
            default => 'the last 7 days',
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function resolveRange(string $range): array
    {
        $today = Carbon::today();

        return match ($range) {
            'today' => [$today->copy(), $today->copy()],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'last_week' => [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            default => [$today->copy()->subDays(6), $today->copy()],
        };
    }
}
