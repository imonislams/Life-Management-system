<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoneyManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // 1. Period Filter Handling
        $period = $request->get('period', 'this_month');

        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;

            case 'last_3_months':
                $startDate = $now->copy()->subMonths(2)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;

            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;

            case 'all_time':
                $startDate = null;
                $endDate = null;
                break;

            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // 2. Salary Analytics
        $activeSalaryRecord = $user->salaries()
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->first();

        $activeSalariesSum = (float) $user->salaries()
            ->where('is_active', true)
            ->sum('amount');

        $monthlySalary = $activeSalariesSum > 0
            ? $activeSalariesSum
            : (float) ($user->salary ?? 0);

        // 3. Additional Income
        $incomeQuery = $user->incomeRecords();

        if ($startDate && $endDate) {
            $incomeQuery->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);
        }

        $additionalIncome = (float) $incomeQuery->sum('amount');

        // Total Income
        $monthsInPeriod = 1;

        if ($period === 'last_3_months') {
            $monthsInPeriod = 3;
        } elseif ($period === 'this_year') {
            $monthsInPeriod = $now->month;
        } elseif ($period === 'all_time') {
            $monthsInPeriod = 1;
        }

        $periodSalaryTotal = $monthlySalary * $monthsInPeriod;
        $totalIncome = $periodSalaryTotal + $additionalIncome;

        // 4. Expense Analytics
        $expenseQuery = $user->expenseRecords();

        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);
        }

        $totalExpenses = (float) $expenseQuery->sum('amount');

        // Current Month vs Previous Month Expense
        $currentMonthExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->sum('amount');

        $lastMonthDate = $now->copy()->subMonth();

        $prevMonthExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $lastMonthDate->year)
            ->whereMonth('date', $lastMonthDate->month)
            ->sum('amount');

        $expenseGrowthPercent = 0;

        if ($prevMonthExpenses > 0) {
            $expenseGrowthPercent = round(
                (($currentMonthExpenses - $prevMonthExpenses) / $prevMonthExpenses) * 100,
                1
            );
        }

        $highestExpenseRecord = $user->expenseRecords()
            ->orderBy('amount', 'desc')
            ->first();

        // 5. Savings Analytics
        $savingsGoal = $user->savingsGoal;

        $targetSavings = $savingsGoal
            ? (float) $savingsGoal->target_amount
            : 0.0;

        $currentSavings = $savingsGoal
            ? (float) $savingsGoal->current_amount
            : 0.0;

        $savingsPercentage = $totalIncome > 0
            ? min(100, round(($currentSavings / $totalIncome) * 100, 1))
            : 0;

        // 6. Available Balance
        $availableBalance = $totalIncome - $totalExpenses - $currentSavings;

        // 7. Recurring Finance Overview
        $activeRecurringCount = $user->recurringTransactions()
            ->where('is_active', true)
            ->count();

        $recurringIncomeCount = $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'income')
            ->count();

        $recurringExpenseCount = $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'expense')
            ->count();

        $recurringIncomeTotal = (float) $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'income')
            ->sum('amount');

        $recurringExpenseTotal = (float) $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'expense')
            ->sum('amount');

        $netRecurringAmount = $recurringIncomeTotal - $recurringExpenseTotal;

        // 8. Monthly Income vs Expense Chart
        $monthlyChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);

            $mAddIncome = (float) $user->incomeRecords()
                ->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month)
                ->sum('amount');

            $mIncome = $monthlySalary + $mAddIncome;

            $mExpense = (float) $user->expenseRecords()
                ->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month)
                ->sum('amount');

            $monthlyChart[] = [
                'label' => $monthDate->format('M Y'),
                'income' => $mIncome,
                'expense' => $mExpense,
                'net' => $mIncome - $mExpense,
            ];
        }

        $maxChartVal = 1;

        foreach ($monthlyChart as $chartItem) {
            $maxChartVal = max(
                $maxChartVal,
                $chartItem['income'],
                $chartItem['expense']
            );
        }

        // 9. Recent Income Transactions
        $recentIncomes = $user->incomeRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'income',
                    'title' => !empty($item->description)
                        ? $item->description
                        : 'Income',
                    'amount' => (float) $item->amount,
                    'date' => $item->date,
                    'description' => !empty($item->description)
                        ? $item->description
                        : 'Income record',
                ];
            });

        // 10. Recent Expense Transactions
        $recentExpenses = $user->expenseRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'expense',
                    'title' => !empty($item->description)
                        ? $item->description
                        : 'Expense',
                    'amount' => (float) $item->amount,
                    'date' => $item->date,
                    'description' => !empty($item->description)
                        ? $item->description
                        : 'Expense record',
                ];
            });

        // 11. Recent Merged Activity
        $recentTransactions = $recentIncomes
            ->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(8)
            ->values();

        return view('money-management.index', compact(
            'period',
            'monthlySalary',
            'activeSalaryRecord',
            'additionalIncome',
            'totalIncome',
            'totalExpenses',
            'currentMonthExpenses',
            'prevMonthExpenses',
            'expenseGrowthPercent',
            'highestExpenseRecord',
            'targetSavings',
            'currentSavings',
            'savingsPercentage',
            'availableBalance',
            'activeRecurringCount',
            'recurringIncomeCount',
            'recurringExpenseCount',
            'recurringIncomeTotal',
            'recurringExpenseTotal',
            'netRecurringAmount',
            'monthlyChart',
            'maxChartVal',
            'recentTransactions'
        ));
    }
}