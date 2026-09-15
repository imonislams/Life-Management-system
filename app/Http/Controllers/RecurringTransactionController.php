<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of recurring transactions for the authenticated user.
     */
    public function index(Request $request)
    {
        $recurringTransactions = $request->user()
            ->recurringTransactions()
            ->orderBy('next_due_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('recurring.index', [
            'recurringTransactions' => $recurringTransactions,
        ]);
    }

    /**
     * Show the form for creating a new recurring transaction.
     */
    public function create()
    {
        return view('recurring.create');
    }

    /**
     * Store a newly created recurring transaction in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'recurrence_type' => ['required', 'in:weekly,monthly'],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $request->user()->recurringTransactions()->create($validated);

        return redirect()->route('recurring-transactions.index')->with('status', 'Recurring record added successfully.');
    }

    /**
     * Show the form for editing the specified recurring transaction.
     */
    public function edit(RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        return view('recurring.edit', [
            'recurringTransaction' => $recurringTransaction,
        ]);
    }

    /**
     * Update the specified recurring transaction in storage.
     */
    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'recurrence_type' => ['required', 'in:weekly,monthly'],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        $recurringTransaction->update($validated);

        return redirect()->route('recurring-transactions.index')->with('status', 'Recurring record updated successfully.');
    }

    /**
     * Remove the specified recurring transaction from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringTransaction->delete();

        return redirect()->route('recurring-transactions.index')->with('status', 'Recurring record deleted successfully.');
    }
}
