<?php

namespace App\Services\AI\Services;

use App\Models\User;
use App\Support\CurrencyConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Finance AI helper.
 *
 * HARD RULE: every financial number produced here is computed by Laravel/SQL —
 * never by the LLM. The LLM only ever receives these already-verified figures and
 * is instructed to explain, not calculate.
 *
 * Currency safety: amounts are grouped by their stored currency_code and never
 * silently summed across different currencies. The user's Settings -> Currency
 * default is reported alongside the figures so the model knows the display unit.
 */
class FinanceAIService
{
    /**
     * Build the structured finance snapshot for a time range.
     *
     * @return array<string, mixed>
     */
    public function snapshot(User $user, string $range = 'this_month'): array
    {
        [$start, $end] = $this->resolveRange($range);

        $defaultCurrency = CurrencyConfig::defaultCurrency($user->id);
        $code = $defaultCurrency['code'];

        // ------------------------------------------------------------------
        // Income (exact aggregate, grouped by currency so nothing is mixed).
        // ------------------------------------------------------------------
        $incomeByCurrency = $user->incomeRecords()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('currency_code', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as records'))
            ->groupBy('currency_code')
            ->get();

        $expenseByCurrency = $user->expenseRecords()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('currency_code', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as records'))
            ->groupBy('currency_code')
            ->get();

        // Salary is a recurring monthly figure; report it separately and never
        // add it to income records of a different currency blindly.
        $activeSalary = (float) $user->salaries()->where('is_active', true)->sum('amount');

        // ------------------------------------------------------------------
        // Biggest expense categories/descriptions (exact SQL grouping).
        // ------------------------------------------------------------------
        $topExpenses = $user->expenseRecords()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('currency_code', 'description', DB::raw('SUM(amount) as total'))
            ->groupBy('currency_code', 'description')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'currency' => $row->currency_code ?: $code,
                'description' => $row->description ?: '(no description)',
                'total' => round((float) $row->total, 2),
            ])
            ->all();

        // ------------------------------------------------------------------
        // Savings (balances aggregated per currency).
        // ------------------------------------------------------------------
        $savingsByCurrency = $user->savingsGoals()
            ->select('currency_code', DB::raw('SUM(current_amount) as current'), DB::raw('SUM(target_amount) as target'))
            ->groupBy('currency_code')
            ->get()
            ->map(fn($row) => [
                'currency' => $row->currency_code ?: $code,
                'current' => round((float) $row->current, 2),
                'target' => round((float) $row->target, 2),
            ])
            ->all();

        return [
            'range' => $range,
            'range_label' => $this->rangeLabel($range),
            'default_currency' => $code,
            'default_currency_symbol' => $defaultCurrency['symbol'],
            'income' => $incomeByCurrency->map(fn($row) => [
                'currency' => $row->currency_code ?: $code,
                'total' => round((float) $row->total, 2),
                'records' => (int) $row->records,
            ])->all(),
            'expenses' => $expenseByCurrency->map(fn($row) => [
                'currency' => $row->currency_code ?: $code,
                'total' => round((float) $row->total, 2),
                'records' => (int) $row->records,
            ])->all(),
            'active_monthly_salary' => round($activeSalary, 2),
            'top_expenses' => $topExpenses,
            'savings' => $savingsByCurrency,
            'multi_currency' => $this->isMultiCurrency($incomeByCurrency, $expenseByCurrency),
        ];
    }

    /**
     * Whether more than one currency appears across the user's records. When
     * true the AI must clearly separate currencies and never invent a rate.
     */
    protected function isMultiCurrency($income, $expense): bool
    {
        $codes = collect($income)->pluck('currency_code')
            ->merge(collect($expense)->pluck('currency_code'))
            ->filter()
            ->unique();

        return $codes->count() > 1;
    }

    /**
     * Human-readable label for a range key.
     */
    public function rangeLabel(string $range): string
    {
        return match ($range) {
            'today' => 'today',
            'yesterday' => 'yesterday',
            'this_week' => 'this week',
            'last_week' => 'last week',
            'this_month', 'monthly' => 'this month',
            'last_month' => 'last month',
            'this_year' => 'this year',
            default => 'the last 30 days',
        };
    }

    /**
     * Resolve a coarse range key into a concrete [start, end] date pair.
     *
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
            'this_month', 'monthly' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [$today->copy()->subDays(29), $today->copy()],
        };
    }
}
