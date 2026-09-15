<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expense records for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = $request->user()->expenseRecords();

        // Filter by specific date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        // Filter by month (YYYY-MM)
        if ($request->filled('month')) {
            $monthParts = explode('-', $request->input('month'));
            if (count($monthParts) === 2) {
                $query->whereYear('date', $monthParts[0])
                      ->whereMonth('date', $monthParts[1]);
            }
        }

        $expenseRecords = $query->orderBy('date', 'desc')
                               ->orderBy('id', 'desc')
                               ->paginate(10)
                               ->withQueryString();

        return view('expenses.index', [
            'expenseRecords' => $expenseRecords,
        ]);
    }

    /**
     * Show the form for creating a new expense record.
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Store a newly created expense record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->expenseRecords()->create($validated);

        return redirect()->route('expenses.index')->with('status', 'Expense record added successfully.');
    }

    /**
     * Show the form for editing the specified expense record.
     */
    public function edit(ExpenseRecord $expense)
    {
        // Server-side ownership check
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }

        return view('expenses.edit', [
            'expense' => $expense,
        ]);
    }

    /**
     * Update the specified expense record in storage.
     */
    public function update(Request $request, ExpenseRecord $expense)
    {
        // Server-side ownership check
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('status', 'Expense record updated successfully.');
    }

    /**
     * Remove the specified expense record from storage.
     */
    public function destroy(ExpenseRecord $expense)
    {
        // Server-side ownership check
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense record deleted successfully.');
    }
}
