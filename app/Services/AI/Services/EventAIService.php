<?php

namespace App\Services\AI\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Event / calendar AI helper.
 *
 * Answers scheduling questions strictly from real Event rows in the requested
 * window. No event is ever invented.
 */
class EventAIService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(User $user, string $range = 'this_week'): array
    {
        [$start, $end] = $this->resolveRange($range);

        $events = $user->events()
            ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit((int) config('ai.retrieval.structured_row_limit', 50))
            ->get()
            ->map(fn($event) => [
                'title' => $event->title,
                'date' => optional($event->event_date)->toDateString(),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
                'status' => $event->status,
                'description' => $event->description,
            ])
            ->all();

        return [
            'range' => $range,
            'range_label' => $this->rangeLabel($range),
            'count' => count($events),
            'events' => $events,
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
            'upcoming' => 'the upcoming period',
            default => 'this week',
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
            'last_week' => [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'upcoming' => [$today->copy(), $today->copy()->addDays(30)],
            default => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
        };
    }
}
