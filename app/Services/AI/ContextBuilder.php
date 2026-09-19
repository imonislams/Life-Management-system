<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Services\ActivityAIService;
use App\Services\AI\Services\EventAIService;
use App\Services\AI\Services\FinanceAIService;
use App\Services\AI\Services\GoalAIService;
use App\Services\AI\Services\HabitAIService;
use App\Services\AI\Services\ProgressAIService;
use Illuminate\Support\Str;

/**
 * Builds the GROUNDED context block handed to the local LLM.
 *
 * Responsibilities:
 *   - run the exact SQL retrievals appropriate to the classified intent,
 *   - merge in the semantic (vector) hits already scoped to one user,
 *   - fuse everything into a compact, clearly-labelled text block,
 *   - fence all untrusted user text so it is treated as DATA, never instructions.
 *
 * The LLM is never given database access; it only ever sees this pre-computed
 * context. Financial numbers are already finalised here by SQL before the model
 * is involved.
 */
class ContextBuilder
{
    public function __construct(
        protected FinanceAIService $finance,
        protected ActivityAIService $activity,
        protected GoalAIService $goal,
        protected HabitAIService $habit,
        protected EventAIService $event,
        protected ProgressAIService $progress,
        protected AIPrivacyService $privacy
    ) {}

    /**
     * Assemble the context text for a classified question.
     *
     * @param  array<int, array{source_type:string, source_id:int, content:string, score:float}>  $semanticHits
     * @return array{text:string, sources:array<int, array{source_type:string, source_id:int, score:float}>, sections:array<int, string>}
     */
    public function build(User $user, array $analysis, array $semanticHits): array
    {
        $maxItems = (int) config('ai.retrieval.max_context_items', 20);
        $maxChars = (int) config('ai.retrieval.max_context_chars', 12000);

        $sections = [];
        $structuredSources = $analysis['structured_sources'] ?? [];
        $range = $analysis['range'] ?? 'recent';

        // ------------------------------------------------------------------
        // 1. Structured SQL retrieval (exact facts).
        // ------------------------------------------------------------------
        if (in_array('finance', $structuredSources, true)) {
            $sections[] = $this->financeSection($user, $range);
        }

        if (in_array('activity', $structuredSources, true)) {
            $sections[] = $this->activitySection($user, $range);
        }

        if (in_array('habit', $structuredSources, true)) {
            $sections[] = $this->habitSection($user);
        }

        if (in_array('event', $structuredSources, true)) {
            $sections[] = $this->eventSection($user, $this->eventRangeFor($range));
        }

        if (in_array('progress', $structuredSources, true)
            || ($analysis['intent'] ?? null) === IntentAnalyzer::INTENT_PERSONAL_PROGRESS) {
            $sections[] = $this->progressSection($user);
        }

        // Goals are useful for goal intent and for weekly summaries.
        $intent = $analysis['intent'] ?? IntentAnalyzer::INTENT_GENERAL;

        if ($intent === IntentAnalyzer::INTENT_GOAL_INSIGHT
            || $intent === IntentAnalyzer::INTENT_WEEKLY_SUMMARY) {
            $sections[] = $this->goalSection($user);
        }

        // ------------------------------------------------------------------
        // 2. Semantic (vector) retrieval, already scoped to this user.
        // ------------------------------------------------------------------
        $semanticSection = $this->semanticSection($semanticHits, $maxItems);

        if ($semanticSection !== null) {
            $sections[] = $semanticSection;
        }

        // ------------------------------------------------------------------
        // 3. Currency + today's date context (so relative dates are grounded).
        // ------------------------------------------------------------------
        $sections[] = $this->metaSection($user);

        $text = implode("\n\n", array_filter($sections));

        // Hard byte budget: never send an unbounded blob to the model.
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars) . "\n[context truncated]";
        }

        $sources = array_map(fn ($hit) => [
            'source_type' => $hit['source_type'],
            'source_id' => $hit['source_id'],
            'score' => $hit['score'],
        ], array_slice($semanticHits, 0, $maxItems));

        return [
            'text' => $text,
            'sources' => $sources,
            'sections' => array_keys(array_filter([
                'finance' => in_array('finance', $structuredSources, true),
                'activity' => in_array('activity', $structuredSources, true),
                'habit' => in_array('habit', $structuredSources, true),
                'event' => in_array('event', $structuredSources, true),
                'progress' => in_array('progress', $structuredSources, true),
                'semantic' => $semanticSection !== null,
            ])),
        ];
    }

    // ------------------------------------------------------------------
    // Section builders
    // ------------------------------------------------------------------

    protected function financeSection(User $user, string $range): string
    {
        $snapshot = $this->finance->snapshot($user, $range);

        $lines = ['=== FINANCE (verified SQL figures, ' . $snapshot['range_label'] . ') ==='];
        $lines[] = 'Default currency: ' . $snapshot['default_currency'];

        if ($snapshot['multi_currency']) {
            $lines[] = 'NOTE: records span multiple currencies. They are listed per currency and must NOT be summed together.';
        }

        if ($snapshot['income'] === []) {
            $lines[] = 'Income recorded: none.';
        } else {
            foreach ($snapshot['income'] as $row) {
                $lines[] = sprintf('Income: %s %s (%d records)', $row['currency'], number_format($row['total'], 2), $row['records']);
            }
        }

        if ($snapshot['expenses'] === []) {
            $lines[] = 'Expenses recorded: none.';
        } else {
            foreach ($snapshot['expenses'] as $row) {
                $lines[] = sprintf('Expenses: %s %s (%d records)', $row['currency'], number_format($row['total'], 2), $row['records']);
            }
        }

        if ((float) $snapshot['active_monthly_salary'] > 0) {
            $lines[] = sprintf('Active monthly salary: %s %s', $snapshot['default_currency'], number_format($snapshot['active_monthly_salary'], 2));
        }

        if ($snapshot['top_expenses'] !== []) {
            $lines[] = 'Top expense descriptions (SQL grouped):';
            foreach ($snapshot['top_expenses'] as $row) {
                $lines[] = sprintf('  - %s: %s %s', $row['description'], $row['currency'], number_format($row['total'], 2));
            }
        }

        if ($snapshot['savings'] !== []) {
            $lines[] = 'Savings goals:';
            foreach ($snapshot['savings'] as $row) {
                $lines[] = sprintf('  - %s: %s saved of %s target', $row['currency'], number_format($row['current'], 2), number_format($row['target'], 2));
            }
        }

        return implode("\n", $lines);
    }

    protected function activitySection(User $user, string $range): string
    {
        $snapshot = $this->activity->snapshot($user, $range);

        $lines = ['=== DAILY ACTIVITIES (' . $snapshot['range_label'] . ') ==='];
        $lines[] = sprintf('%d activities, %d minutes total.', $snapshot['count'], $snapshot['total_minutes']);

        if ($snapshot['top_categories'] !== []) {
            $lines[] = 'Time by category:';
            foreach ($snapshot['top_categories'] as $category => $minutes) {
                $lines[] = sprintf('  - %s: %d minutes', $category, $minutes);
            }
        }

        foreach (array_slice($snapshot['activities'], 0, 15) as $row) {
            $lines[] = $this->fenceRow(sprintf(
                '[%s] %s%s',
                $row['date'] ?? '?',
                $row['title'],
                $row['category'] ? ' (category: ' . $row['category'] . ')' : '',
                $row['goal'] ? ' (goal: ' . $row['goal'] . ')' : ''
            ));
        }

        return implode("\n", $lines);
    }

    protected function goalSection(User $user): string
    {
        $snapshot = $this->goal->snapshot($user);

        $lines = ['=== GOALS (verified figures) ==='];
        $lines[] = sprintf('%d total, %d active, %d completed, %d overdue.', $snapshot['total'], $snapshot['active_count'], $snapshot['completed_count'], $snapshot['overdue_count']);

        if ($snapshot['active'] === []) {
            $lines[] = 'No active goals.';
        }

        foreach ($snapshot['active'] as $goal) {
            $lines[] = sprintf(
                '- %s | status: %s | progress: %s%% | days elapsed: %s | days remaining: %s | overdue: %s',
                $goal['title'],
                $goal['status'],
                $goal['progress_percentage'] ?? 'n/a',
                $goal['days_elapsed'] ?? 'n/a',
                $goal['days_remaining'] ?? 'n/a',
                $goal['is_overdue'] ? 'yes' : 'no'
            );

            if (! empty($goal['description'])) {
                $lines[] = '  ' . $this->fenceRow(Str::limit((string) $goal['description'], 240));
            }
        }

        return implode("\n", $lines);
    }

    protected function habitSection(User $user): string
    {
        $snapshot = $this->habit->snapshot($user);

        $lines = ['=== HABITS (real completion data, last ' . $snapshot['window_days'] . ' days) ==='];
        $lines[] = sprintf('%d habits, %d active.', $snapshot['total_habits'], $snapshot['active_habits']);

        if ($snapshot['habits'] === []) {
            $lines[] = 'No habits recorded.';
            return implode("\n", $lines);
        }

        foreach ($snapshot['habits'] as $row) {
            $lines[] = sprintf(
                '- %s | consistency: %d%% (%d/%d days) | status: %s',
                $row['title'],
                $row['consistency_percent'],
                $row['completed_days'],
                $row['window_days'],
                $row['status']
            );
        }

        return implode("\n", $lines);
    }

    protected function eventSection(User $user, string $range): string
    {
        $snapshot = $this->event->snapshot($user, $range);

        $lines = ['=== EVENTS (' . $snapshot['range_label'] . ') ==='];
        $lines[] = sprintf('%d events.', $snapshot['count']);

        foreach ($snapshot['events'] as $row) {
            $time = $row['start_time'] ? substr((string) $row['start_time'], 0, 5) : '';
            $timePart = $time !== '' ? ' @ ' . $time : '';
            $lines[] = $this->fenceRow(sprintf(
                '[%s%s] %s%s',
                $row['date'] ?? '?',
                $timePart,
                $row['title'],
                $row['location'] ? ' @ ' . $row['location'] : '',
                $row['status'] ? ' (' . $row['status'] . ')' : ''
            ));
        }

        return implode("\n", $lines);
    }

    protected function progressSection(User $user): string
    {
        $snapshot = $this->progress->snapshot($user);

        $lines = ['=== PERSONAL PROGRESS (last ' . $snapshot['window_days'] . ' days) ==='];
        $lines[] = sprintf('%d logged progress updates; %d goals completed recently.', $snapshot['recent_progress_count'], $snapshot['goals_completed_recently']);

        foreach (array_slice($snapshot['recent_progress_updates'], 0, 12) as $row) {
            $lines[] = $this->fenceRow(sprintf(
                '[%s] %s — %s',
                $row['date'] ?? '?',
                $row['goal'],
                $row['description'] ?: '(no description)'
            ));
        }

        if ($snapshot['active_categories'] !== []) {
            $lines[] = 'Active categories: ' . collect($snapshot['active_categories'])
                ->map(fn ($c) => $c['category'] . ' (' . $c['activities'] . ')')
                ->implode(', ');
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, array{source_type:string, source_id:int, content:string, score:float}>  $hits
     */
    protected function semanticSection(array $hits, int $maxItems): ?string
    {
        if ($hits === []) {
            return null;
        }

        $lines = ['=== SEMANTICALLY RELATED RECORDS (from the user\'s own index) ==='];

        foreach (array_slice($hits, 0, $maxItems) as $hit) {
            $lines[] = $this->fenceRow(sprintf(
                '(%s #%d, relevance %.2f) %s',
                $hit['source_type'],
                $hit['source_id'],
                $hit['score'],
                $hit['content']
            ));
        }

        return implode("\n", $lines);
    }

    protected function metaSection(User $user): string
    {
        $currency = \App\Support\CurrencyConfig::defaultCurrency($user->id);
        $settings = \App\Models\Setting::forUser($user->id);

        return implode("\n", [
            '=== CONTEXT META ===',
            'Today: ' . now()->toDateString() . ' (' . now()->format('l') . ').',
            'User default currency: ' . $currency['code'] . '.',
            'Timezone: ' . ($settings->timezone ?? config('app.timezone')) . '.',
        ]);
    }

    /**
     * Wrap a single untrusted row so the model treats it strictly as data.
     */
    protected function fenceRow(string $row): string
    {
        return '  ' . $this->privacy->fence(Str::limit($this->privacy->sanitizeUntrusted($row, 400), 400));
    }

    /**
     * Events use a slightly different range vocabulary than finance/activity.
     */
    protected function eventRangeFor(string $range): string
    {
        return match ($range) {
            'today' => 'today',
            'yesterday' => 'yesterday',
            'last_week' => 'last_week',
            'this_month' => 'this_month',
            default => 'this_week',
        };
    }
}
