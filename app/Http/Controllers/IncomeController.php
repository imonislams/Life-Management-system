<?php

namespace App\Http\Controllers;

use App\Models\IncomeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of income records for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = $request->user()->incomeRecords();

        // Filter by day-to-day date range (from_date ... to_date)
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->input('to_date'));
        }

        $incomeRecords = $query->orderBy('date', 'desc')
                              ->orderBy('id', 'desc')
                              ->paginate(10)
                              ->withQueryString();

        return view('income.index', [
            'incomeRecords' => $incomeRecords,
        ]);
    }

    /**
     * Show the form for creating a new income record.
     */
    public function create()
    {
        return view('income.create');
    }

    /**
     * Store a newly created income record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->incomeRecords()->create($validated);

        return redirect()->route('income.index')->with('status', 'Income record added successfully.');
    }

    /**
     * Show the form for editing the specified income record.
     */
    public function edit(IncomeRecord $income)
    {
        // Server-side ownership check
        if ($income->user_id !== Auth::id()) {
            abort(403);
        }

        return view('income.edit', [
            'income' => $income,
        ]);
    }

    /**
     * Update the specified income record in storage.
     */
    public function update(Request $request, IncomeRecord $income)
    {
        // Server-side ownership check
        if ($income->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $income->update($validated);

        return redirect()->route('income.index')->with('status', 'Income record updated successfully.');
    }

    /**
     * Remove the specified income record from storage.
     */
    public function destroy(IncomeRecord $income)
    {
        // Server-side ownership check
        if ($income->user_id !== Auth::id()) {
            abort(403);
        }

        $income->delete();

        return redirect()->route('income.index')->with('status', 'Income record deleted successfully.');
    }
}
