<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the financial dashboard with summary cards, charts, and recent transactions.
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // 1. Current Month Financial Summary
        $monthlySalary = (float) ($user->salary ?? 0);

        $additionalIncome = (float) $user->incomeRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        $totalIncome = $monthlySalary + $additionalIncome;

        $totalExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        // 2. Savings Goal Summary
        $savingsGoal = $user->savingsGoal;
        $totalSavings = $savingsGoal ? (float) $savingsGoal->current_amount : 0.0;
        $availableBalance = $totalIncome - $totalExpenses - $totalSavings;
        $savingsSummary = null;

        if ($savingsGoal) {
            $target = (float) $savingsGoal->target_amount;
            $current = (float) $savingsGoal->current_amount;
            $remaining = max(0, $target - $current);
            $progress = $target > 0 ? min(100, round(($current / $target) * 100, 2)) : 0;

            $savingsSummary = [
                'name' => $savingsGoal->name,
                'target_amount' => $target,
                'current_amount' => $current,
                'remaining_amount' => $remaining,
                'progress_percentage' => $progress,
            ];
        }

        // 3. Monthly Income vs Expense Chart (Last 6 Months)
        $monthlyChartLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $monthlyChartLabels[] = $monthDate->format('M Y');

            $mAddIncome = (float) $user->incomeRecords()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            // Include monthly salary if set
            $mTotalIncome = $monthlySalary + $mAddIncome;

            $mExpenses = (float) $user->expenseRecords()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthlyIncomeData[] = $mTotalIncome;
            $monthlyExpenseData[] = $mExpenses;
        }

        // 4. Recent Transactions
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

        return view('dashboard', [
            'monthlySalary' => $monthlySalary,
            'additionalIncome' => $additionalIncome,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'totalSavings' => $totalSavings,
            'availableBalance' => $availableBalance,
            'savingsSummary' => $savingsSummary,
            'monthlyChartLabels' => $monthlyChartLabels,
            'monthlyIncomeData' => $monthlyIncomeData,
            'monthlyExpenseData' => $monthlyExpenseData,
            'recentIncome' => $recentIncome,
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
