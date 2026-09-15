<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    /**
     * Display the salary management page.
     */
    public function index()
    {
        $user = Auth::user();

        return view('salary.index', [
            'salary' => $user->salary,
        ]);
    }

    /**
     * Store or update the authenticated user's current monthly salary.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'salary' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
        ], [
            'salary.required' => 'The monthly salary field is required.',
            'salary.numeric' => 'The monthly salary must be a valid number.',
            'salary.gt' => 'The monthly salary must be greater than 0.',
            'salary.max' => 'The monthly salary exceeds maximum allowed amount.',
        ]);

        $user = Auth::user();
        $user->update([
            'salary' => $validated['salary'],
        ]);

        return redirect()->route('salary.index')->with('status', 'Salary updated successfully.');
    }
}
