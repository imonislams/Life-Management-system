<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    /**
     * Display salary management page for authenticated user.
     */
    public function index(Request $request)
    {
        $salaries = $request->user()->salaries()->orderBy('id', 'desc')->get();

        $activeSalaryRecord = $salaries->where('is_active', true)->first();
        $totalActiveSalary = (float) $salaries->where('is_active', true)->sum('amount');

        return view('salary.index', [
            'salaries' => $salaries,
            'activeSalaryRecord' => $activeSalaryRecord,
            'totalActiveSalary' => $totalActiveSalary,
        ]);
    }

    /**
     * Show form for creating a new salary record.
     */
    public function create()
    {
        return view('salary.create');
    }

    /**
     * Store a newly created salary record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'payment_day' => ['nullable', 'integer', 'between:1,31'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'amount.required' => 'The monthly salary amount is required.',
            'amount.numeric' => 'The monthly salary amount must be a number.',
            'amount.gt' => 'The monthly salary amount must be greater than 0.',
            'payment_day.between' => 'The salary payment day must be between 1 and 31.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $salary = $request->user()->salaries()->create($validated);

        // Keep legacy user.salary column in sync with active salary sum
        $activeSum = $request->user()->salaries()->where('is_active', true)->sum('amount');
        $request->user()->update(['salary' => $activeSum]);

        return redirect()->route('salary.index')->with('status', 'Salary record added successfully.');
    }

    /**
     * Show form for editing a salary record.
     */
    public function edit(Salary $salary)
    {
        if ($salary->user_id !== Auth::id()) {
            abort(403);
        }

        return view('salary.edit', [
            'salary' => $salary,
        ]);
    }

    /**
     * Update a salary record.
     */
    public function update(Request $request, Salary $salary)
    {
        if ($salary->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'payment_day' => ['nullable', 'integer', 'between:1,31'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'amount.required' => 'The monthly salary amount is required.',
            'amount.numeric' => 'The monthly salary amount must be a number.',
            'amount.gt' => 'The monthly salary amount must be greater than 0.',
            'payment_day.between' => 'The salary payment day must be between 1 and 31.',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        $salary->update($validated);

        // Keep legacy user.salary column in sync with active salary sum
        $activeSum = $request->user()->salaries()->where('is_active', true)->sum('amount');
        $request->user()->update(['salary' => $activeSum]);

        return redirect()->route('salary.index')->with('status', 'Salary record updated successfully.');
    }

    /**
     * Delete a salary record.
     */
    public function destroy(Salary $salary)
    {
        if ($salary->user_id !== Auth::id()) {
            abort(403);
        }

        $user = $salary->user;
        $salary->delete();

        // Keep legacy user.salary column in sync with active salary sum
        $activeSum = $user->salaries()->where('is_active', true)->sum('amount');
        $user->update(['salary' => $activeSum]);

        return redirect()->route('salary.index')->with('status', 'Salary record deleted successfully.');
    }
}
