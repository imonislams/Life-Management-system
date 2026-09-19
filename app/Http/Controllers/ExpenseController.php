<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExpenseRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where('description', 'like', '%' . $term . '%');
        }

        $expenseRecords = $query->with('currency')
                               ->orderBy('date', 'desc')
                               ->orderBy('id', 'desc')
                               ->paginate(10)
                               ->withQueryString();

        $totalAmount = (float) $request->user()->expenseRecords()->sum('amount');

        return view('expenses.index', [
            'expenseRecords' => $expenseRecords,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Show the form for creating a new expense record.
     */
    public function create()
    {
        return view('expenses.create', [
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
        ]);
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
            'notes' => ['nullable', 'string', 'max:2000'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        // The original currency is snapshotted so historical records keep it.
        $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);

        $request->user()->expenseRecords()->create([
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'currency_id' => $currency?->id,
            'currency_code' => $currency?->code ?? Currency::systemDefaultCode($request->user()->id),
        ]);

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
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
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
            'notes' => ['nullable', 'string', 'max:2000'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        $payload = [
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->filled('currency_id')) {
            $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);
            $payload['currency_id'] = $currency?->id;
            $payload['currency_code'] = $currency?->code;
        }

        $expense->update($payload);

        return redirect()->route('expenses.index')->with('status', 'Expense record updated successfully.');
    }

    /**
     * Active currencies belonging to the authenticated user.
     */
    private function activeCurrencies()
    {
        return Currency::ownedBy(Auth::id())->active()
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
    }

    /**
     * Resolve a submitted currency id against the user's own currencies,
     * falling back to their default. Never trusts the raw input.
     */
    private function resolveCurrency($currencyId, int $userId): ?Currency
    {
        if ($currencyId) {
            $currency = Currency::ownedBy($userId)->active()->find($currencyId);
            if ($currency) {
                return $currency;
            }
        }

        return Currency::defaultFor($userId);
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
