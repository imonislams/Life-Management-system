<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoneyManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // 1. Total Active Monthly Salary
        $activeSalariesSum = (float) $user->salaries()->where('is_active', true)->sum('amount');
        $monthlySalary = $activeSalariesSum > 0 ? $activeSalariesSum : (float) ($user->salary ?? 0);

        // 2. Additional Income (Current Month)
        $additionalIncome = (float) $user->incomeRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        // 3. Total Income (Salary + Additional Income)
        $totalIncome = $monthlySalary + $additionalIncome;

        // 4. Total Expenses (Current Month)
        $totalExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        // 5. Total Savings Goal / Current Savings
        $savingsGoal = $user->savingsGoal;
        $totalSavings = $savingsGoal ? (float) $savingsGoal->target_amount : 0.0;
        $currentSavings = $savingsGoal ? (float) $savingsGoal->current_amount : 0.0;

        // 6. Current Balance (Total Income - Total Expenses)
        $currentBalance = $totalIncome - $totalExpenses;

        // 7. Recurring Finance Summary
        $activeRecurringCount = $user->recurringTransactions()
            ->where('is_active', true)
            ->count();

        $recurringIncomeTotal = (float) $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'income')
            ->sum('amount');

        $recurringExpenseTotal = (float) $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'expense')
            ->sum('amount');

        // 8. Monthly Recurring Amount (net recurring impact per month)
        $recurringMonthlyAmount = $recurringIncomeTotal - $recurringExpenseTotal;

        // 9. Savings Overview
        $savingsTarget = $savingsGoal ? (float) $savingsGoal->target_amount : 0.0;
        $savingsRemaining = $savingsGoal ? max(0, $savingsTarget - $currentSavings) : 0.0;
        $savingsProgress = ($savingsGoal && $savingsTarget > 0)
            ? min(100, round(($currentSavings / $savingsTarget) * 100, 2))
            : 0;

        // 10. Salary Overview
        $activeSalaryRecord = $user->salaries()
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->first();
        $activeSalaryCount = $user->salaries()->where('is_active', true)->count();

        // 11. Monthly Income vs Expense Chart (Last 6 Months)
        $monthlyChartLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);

            $monthlyChartLabels[] = $monthDate->format('M Y');

            $mAddIncome = (float) $user->incomeRecords()
                ->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month)
                ->sum('amount');

            $mExpenses = (float) $user->expenseRecords()
                ->whereYear('date', $monthDate->year)
                ->whereMonth('date', $monthDate->month)
                ->sum('amount');

            $monthlyIncomeData[] = $monthlySalary + $mAddIncome;
            $monthlyExpenseData[] = $mExpenses;
        }

        // 12. Recent Transactions
        $recentIncome = $user->incomeRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentExpenses = $user->expenseRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('money-management.index', compact(
            'monthlySalary',
            'additionalIncome',
            'totalIncome',
            'totalExpenses',
            'totalSavings',
            'currentSavings',
            'currentBalance',
            'activeRecurringCount',
            'recurringIncomeTotal',
            'recurringExpenseTotal',
            'recurringMonthlyAmount',
            'savingsTarget',
            'savingsRemaining',
            'savingsProgress',
            'activeSalaryRecord',
            'activeSalaryCount',
            'monthlyChartLabels',
            'monthlyIncomeData',
            'monthlyExpenseData',
            'recentIncome',
            'recentExpenses'
        ));
    }
}
