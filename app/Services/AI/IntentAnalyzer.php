<?php

namespace App\Services\AI;

use App\Models\AiEmbedding;

/**
 * Deterministic intent analyser for the RAG pipeline.
 *
 * It decides which data sources a question needs and whether the answer should
 * lean on exact SQL aggregates (money, counts) and/or semantic retrieval
 * (narrative "what was I working on" questions).
 *
 * It deliberately NEVER calls the LLM: the LLM must never be the component that
 * decides what to read from the database. Retrieval authorisation stays in PHP.
 */
class IntentAnalyzer
{
    public const INTENT_ACTIVITY_SUMMARY = 'activity_summary';

    public const INTENT_GOAL_INSIGHT = 'goal_insight';

    public const INTENT_FINANCE = 'finance';

    public const INTENT_HABIT = 'habit';

    public const INTENT_EVENT = 'event';

    public const INTENT_SEMANTIC_SEARCH = 'semantic_search';

    public const INTENT_WEEKLY_SUMMARY = 'weekly_summary';

    public const INTENT_PERSONAL_PROGRESS = 'personal_progress';

    public const INTENT_GENERAL = 'general';

    /**
     * Classify a question into a structured result describing the required
     * retrieval strategy.
     *
     * @return array{
     *     intent:string,
     *     needs_structured:bool,
     *     needs_semantic:bool,
     *     source_types:array<int,string>,
     *     structured_sources:array<int,string>,
     *     range:string
     * }
     */
    public function analyze(string $question): array
    {
        $q = mb_strtolower($question);

        $sourceTypes = [];
        $structured = [];
        $needsSemantic = false;
        $needsStructured = false;

        // --- Money / finance (exact SQL arithmetic) ---
        $financeHits = $this->containsAny($q, [
            'spend',
            'spent',
            'expense',
            'expenses',
            'cost',
            'paid',
            'pay',
            'earn',
            'earned',
            'income',
            'salary',
            'save',
            'saved',
            'saving',
            'savings',
            'money',
            'balance',
            'budget',
            'recurring',
            'transaction',
            'largest expense',
            'biggest expense',
            'afford',
            'currency',
        ]);

        if ($financeHits) {
            $needsStructured = true;
            $structured[] = 'finance';
        }

        // --- Activities ---
        $activityHits = $this->containsAny($q, [
            'did i do',
            'what did i',
            'activity',
            'activities',
            'worked on',
            'working on',
            'worked',
            'accomplish',
            'accomplished',
            'done today',
            'today',
            'yesterday',
            'this week',
            'last week',
            'last month',
            'focus',
            'focusing',
            'journal',
        ]);

        if ($activityHits) {
            $sourceTypes[] = AiEmbedding::SOURCE_DAILY_ACTIVITY;
            $needsSemantic = true;
        }

        // --- Goals / progress ---
        $goalHits = $this->containsAny($q, [
            'goal',
            'goals',
            'progress',
            'target',
            'objective',
            'milestone',
            'on track',
            'accomplish',
            'achievement',
            'days left',
            'days remaining',
        ]);

        if ($goalHits) {
            $sourceTypes[] = AiEmbedding::SOURCE_GOAL;
            $sourceTypes[] = AiEmbedding::SOURCE_GOAL_PROGRESS;
            $needsSemantic = true;
        }

        // --- Habits ---
        $habitHits = $this->containsAny($q, [
            'habit',
            'habits',
            'streak',
            'routine',
            'consistency',
            'maintained',
            'daily practice',
            'missed',
        ]);

        if ($habitHits) {
            $sourceTypes[] = AiEmbedding::SOURCE_HABIT;
            $needsStructured = true;
            $structured[] = 'habit';
        }

        // --- Events / calendar ---
        $eventHits = $this->containsAny($q, [
            'event',
            'events',
            'calendar',
            'appointment',
            'meeting',
            'schedule',
            'birthday',
            'coming up',
            'scheduled',
        ]);

        if ($eventHits) {
            $sourceTypes[] = AiEmbedding::SOURCE_EVENT;
            $needsStructured = true;
            $structured[] = 'event';
        }

        // --- Planned activities (structured activity summary) ---
        if ($activityHits) {
            $needsStructured = true;
            $structured[] = 'activity';
        }

        // --- Personal progress ---
        $progressHits = $this->containsAny($q, [
            'achievement',
            'achieved',
            'personal progress',
            'how am i doing',
            'areas have i been',
            'improving',
        ]);

        if ($progressHits) {
            $needsStructured = true;
            $structured[] = 'progress';
        }

        // --- Time range ---
        $range = $this->detectRange($q);

        // --- Intent ---
        $intent = $this->detectIntent($q, $financeHits, $goalHits, $habitHits, $activityHits, $eventHits, $progressHits);

        // Weekly summary always wants everything.
        if ($intent === self::INTENT_WEEKLY_SUMMARY) {
            $needsSemantic = true;
            $needsStructured = true;
            $sourceTypes = array_merge($sourceTypes, [
                AiEmbedding::SOURCE_DAILY_ACTIVITY,
                AiEmbedding::SOURCE_GOAL,
                AiEmbedding::SOURCE_GOAL_PROGRESS,
                AiEmbedding::SOURCE_HABIT,
                AiEmbedding::SOURCE_EVENT,
            ]);
            $structured = array_values(array_unique(array_merge($structured, ['finance', 'habit', 'activity', 'event', 'progress'])));
        }

        // A broad "find / search everything related to X" question should search
        // the WHOLE semantic index, not just the one source type its word choice
        // happened to match. This avoids empty results for cross-cutting queries
        // (e.g. "find activities related to learning" should also surface goals
        // and progress notes).
        if ($intent === self::INTENT_SEMANTIC_SEARCH) {
            $needsSemantic = true;
            $sourceTypes = AiEmbedding::SOURCE_TYPES;
        }

        // A completely unmatched question still gets a scoped semantic pass so
        // "what have I been up to" style phrasing is never left empty-handed.
        if (! $needsSemantic && ! $needsStructured) {
            $needsSemantic = true;
            $sourceTypes = AiEmbedding::SOURCE_TYPES;
        }

        return [
            'intent' => $intent,
            'needs_structured' => $needsStructured,
            'needs_semantic' => $needsSemantic,
            'source_types' => array_values(array_unique($sourceTypes)),
            'structured_sources' => array_values(array_unique($structured)),
            'range' => $range,
        ];
    }

    protected function detectIntent(
        string $q,
        bool $finance,
        bool $goal,
        bool $habit,
        bool $activity,
        bool $event,
        bool $progress
    ): string {
        if ($this->containsAny($q, ['weekly summary', 'week summary', 'summarise my week', 'summarize my week', 'weekly report'])) {
            return self::INTENT_WEEKLY_SUMMARY;
        }

        if ($this->containsAny($q, ['find ', 'search ', 'show me everything', 'everything i'])) {
            return self::INTENT_SEMANTIC_SEARCH;
        }

        if ($finance) {
            return self::INTENT_FINANCE;
        }

        if ($goal) {
            return self::INTENT_GOAL_INSIGHT;
        }

        if ($habit) {
            return self::INTENT_HABIT;
        }

        if ($event) {
            return self::INTENT_EVENT;
        }

        if ($progress) {
            return self::INTENT_PERSONAL_PROGRESS;
        }

        if ($activity) {
            return self::INTENT_ACTIVITY_SUMMARY;
        }

        return self::INTENT_GENERAL;
    }

    /**
     * Detect a coarse time range mentioned in the question.
     */
    protected function detectRange(string $q): string
    {
        return match (true) {
            $this->containsAny($q, ['today', 'right now']) => 'today',
            $this->containsAny($q, ['yesterday']) => 'yesterday',
            $this->containsAny($q, ['this week', 'weekly']) => 'this_week',
            $this->containsAny($q, ['last week']) => 'last_week',
            $this->containsAny($q, ['this month', 'monthly']) => 'this_month',
            $this->containsAny($q, ['last month']) => 'last_month',
            $this->containsAny($q, ['this year', 'yearly']) => 'this_year',
            $this->containsAny($q, ['recently', 'recent', 'lately', 'last few days']) => 'recent',
            default => 'recent',
        };
    }

    /**
     * Match any keyword against the question.
     *
     * Single-word keywords are matched on a word boundary so a stem never fires
     * falsely (e.g. "earn" must NOT match "learning"). Multi-word phrases are
     * matched as plain substrings.
     *
     * @param  array<int, string>  $needles
     */
    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($needle, ' ')) {
                // Phrase: substring match is fine.
                if (str_contains($haystack, $needle)) {
                    return true;
                }

                continue;
            }

            // Single word: require a boundary so "earn" does not match "learning".
            if (preg_match('/\b' . preg_quote($needle, '/') . '\b/u', $haystack) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convenience: force an analysis result for a known use-case (used by the
     * dashboard quick actions and the goal-specific panel).
     *
     * @param  array<int, string>  $sourceTypes
     * @return array<string, mixed>
     */
    public function preset(string $intent, array $sourceTypes, bool $structured, string $range = 'recent'): array
    {
        return [
            'intent' => $intent,
            'needs_structured' => $structured,
            'needs_semantic' => true,
            'source_types' => $sourceTypes,
            'structured_sources' => $structured ? ['finance', 'habit', 'activity', 'event', 'progress'] : [],
            'range' => $range,
        ];
    }
}
