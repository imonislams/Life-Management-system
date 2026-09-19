<?php

namespace App\Services\AI\Services;

use App\Models\Goal;
use App\Models\User;

/**
 * Goal AI helper.
 *
 * Every date calculation (days elapsed, days remaining, progress %) comes from
 * the Goal model using real stored values. The LLM only explains them.
 */
class GoalAIService
{
    /**
     * Structured snapshot of the user's active goals.
     *
     * @return array<string, mixed>
     */
    public function snapshot(User $user): array
    {
        $goals = $user->goals()
            ->orderByDesc('id')
            ->limit((int) config('ai.retrieval.structured_row_limit', 50))
            ->get();

        $active = [];
        $completed = 0;

        foreach ($goals as $goal) {
            $row = $this->describe($goal);

            if ($goal->status === 'completed') {
                $completed++;
            }

            if (in_array($goal->status, ['not_started', 'in_progress', 'paused'], true)) {
                $active[] = $row;
            }
        }

        return [
            'total' => $goals->count(),
            'active_count' => count($active),
            'completed_count' => $completed,
            'overdue_count' => $goals->filter(fn(Goal $g) => $g->isOverdue())->count(),
            'active' => $active,
        ];
    }

    /**
     * Detailed, single-goal snapshot including related activities and updates.
     *
     * @return array<string, mixed>
     */
    public function describe(Goal $goal): array
    {
        $recentActivities = $goal->dailyActivities()
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'title' => $a->title,
                'date' => optional($a->activity_date)->toDateString(),
                'duration_minutes' => (int) ($a->resolveDuration() ?? 0),
            ])
            ->all();

        $recentUpdates = $goal->progressUpdates()
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'date' => optional($u->date)->toDateString(),
                'description' => $u->description,
                'progress_value' => (float) $u->progress_value,
                'time_spent_minutes' => (int) $u->time_spent_minutes,
            ])
            ->all();

        return [
            'id' => $goal->id,
            'title' => $goal->title,
            'description' => $goal->description,
            'status' => $goal->status,
            'progress_type' => $goal->progress_type,
            'progress_percentage' => $goal->progressPercentage(),
            'start_date' => optional($goal->start_date)->toDateString(),
            'target_date' => optional($goal->target_date)->toDateString(),
            'days_elapsed' => $goal->daysElapsed(),
            'days_remaining' => $goal->daysRemaining(),
            'duration_days' => $goal->durationInDays(),
            'is_overdue' => $goal->isOverdue(),
            'total_time_spent_minutes' => $goal->totalTimeSpentMinutes(),
            'recent_activities' => $recentActivities,
            'recent_updates' => $recentUpdates,
        ];
    }
}
