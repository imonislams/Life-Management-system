<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsController extends Controller
{
    public function index(Request $request): View
    {
        $savingsGoal = $request->user()->savingsGoal;

        $remainingAmount = 0;
        $progressPercentage = 0;

        if ($savingsGoal) {
            $target = (float) $savingsGoal->target_amount;
            $current = (float) $savingsGoal->current_amount;
            $remainingAmount = max(0, $target - $current);
            $progressPercentage = $target > 0 ? min(100, round(($current / $target) * 100, 2)) : 0;
        }

        return view('savings.index', compact('savingsGoal', 'remainingAmount', 'progressPercentage'));
    }

    public function storeOrUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|gt:0',
            'current_amount' => 'required|numeric|min:0|lte:target_amount',
        ]);

        $user = $request->user();

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
}
