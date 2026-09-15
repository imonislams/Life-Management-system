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

        $remainingBalance = $totalIncome - $totalExpenses;

        // 2. Monthly Income vs Expense Chart (Last 6 Months)
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

        // 3. Recent Transactions
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
            'remainingBalance' => $remainingBalance,
            'monthlyChartLabels' => $monthlyChartLabels,
            'monthlyIncomeData' => $monthlyIncomeData,
            'monthlyExpenseData' => $monthlyExpenseData,
            'recentIncome' => $recentIncome,
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
