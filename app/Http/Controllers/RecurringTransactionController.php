<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of recurring transactions for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = $request->user()->recurringTransactions();

        if ($request->filled('type') && in_array($request->type, RecurringTransaction::TYPES, true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && in_array($request->status, RecurringTransaction::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $recurringTransactions = $query
            ->orderBy('next_due_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('recurring.index', [
            'recurringTransactions' => $recurringTransactions,
        ]);
    }

    /**
     * Show the form for creating a new recurring transaction.
     */
    public function create()
    {
        return view('recurring.create', [
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
        ]);
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
            'recurrence_type' => ['required', Rule::in(RecurringTransaction::RECURRENCE_TYPES)],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(RecurringTransaction::STATUSES)],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        // Keep the legacy is_active flag aligned with the explicit status.
        $validated['is_active'] = $validated['status'] === 'active';

        if ($validated['recurrence_type'] !== 'custom') {
            $validated['interval_days'] = null;
        }

        // Snapshot the currency. Recurring Finance still never auto-generates
        // transactions; this only records how the amount is denominated.
        $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);
        $validated['currency_id'] = $currency?->id;
        $validated['currency_code'] = $currency?->code ?? Currency::systemDefaultCode($request->user()->id);

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
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
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
            'recurrence_type' => ['required', Rule::in(RecurringTransaction::RECURRENCE_TYPES)],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(RecurringTransaction::STATUSES)],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        // Keep the legacy is_active flag aligned with the explicit status.
        $validated['is_active'] = $validated['status'] === 'active';

        if ($validated['recurrence_type'] !== 'custom') {
            $validated['interval_days'] = null;
        }

        if ($request->filled('currency_id')) {
            $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);
            $validated['currency_id'] = $currency?->id;
            $validated['currency_code'] = $currency?->code;
        }

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
     * Resolve a submitted currency id against the user's own active currencies,
     * falling back to their default currency. Never trusts the raw input.
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
}
