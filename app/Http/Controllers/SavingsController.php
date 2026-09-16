<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsController extends Controller
{
    /**
     * Calculate current month's available balance for a user.
     * Available Balance = Total Income - Total Expenses - Total Savings
     */
    private function getAvailableBalance($user): float
    {
        $now = Carbon::now();
        $monthlySalary = (float) ($user->salary ?? 0);

        $additionalIncome = (float) $user->incomeRecords()
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->sum('amount');

        $totalIncome = $monthlySalary + $additionalIncome;

        $totalExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->sum('amount');

        $currentSavings = $user->savingsGoal ? (float) $user->savingsGoal->current_amount : 0.0;

        return $totalIncome - $totalExpenses - $currentSavings;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $savingsGoal = $user->savingsGoal;

        $remainingAmount = 0;
        $progressPercentage = 0;

        if ($savingsGoal) {
            $target = (float) $savingsGoal->target_amount;
            $current = (float) $savingsGoal->current_amount;
            $remainingAmount = max(0, $target - $current);
            $progressPercentage = $target > 0 ? min(100, round(($current / $target) * 100, 2)) : 0;
        }

        $availableBalance = $this->getAvailableBalance($user);

        return view('savings.index', compact('savingsGoal', 'remainingAmount', 'progressPercentage', 'availableBalance'));
    }

    public function storeOrUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|gt:0',
            'current_amount' => 'required|numeric|min:0|lte:target_amount',
        ]);

        $user = $request->user();
        $existingSavings = $user->savingsGoal ? (float) $user->savingsGoal->current_amount : 0.0;
        $newAmount = (float) $validated['current_amount'];

        if ($newAmount > $existingSavings) {
            $increase = $newAmount - $existingSavings;
            $availableBalance = $this->getAvailableBalance($user);

            if ($increase > $availableBalance) {
                return back()->withErrors(['current_amount' => 'Insufficient available balance.'])->withInput();
            }
        }

        SavingsGoal::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $validated['name'],
                'target_amount' => $validated['target_amount'],
                'current_amount' => $validated['current_amount'],
            ]
        );

        return redirect()->route('savings.index')->with('status', 'Savings goal updated successfully.');
    }

    public function addMoney(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
        ]);

        $user = $request->user();
        $savingsGoal = $user->savingsGoal;

        if (!$savingsGoal) {
            return back()->withErrors(['amount' => 'Please create a savings goal first.']);
        }

        $addAmount = (float) $validated['amount'];
        $availableBalance = $this->getAvailableBalance($user);

        if ($addAmount > $availableBalance) {
            return back()->withErrors(['amount' => 'Insufficient available balance.'])->withInput();
        }

        $savingsGoal->current_amount = (float) $savingsGoal->current_amount + $addAmount;
        $savingsGoal->save();

        return redirect()->route('savings.index')->with('status', 'Money added to savings successfully.');
    }
}
