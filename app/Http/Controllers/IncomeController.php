<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\IncomeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    /**
     * Display a listing of income records for the authenticated user.
     */
    public function index(Request $request)
    {
        $query = $request->user()->incomeRecords();

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

        // Search across description / source / category.
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', '%' . $term . '%')
                    ->orWhere('source', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%');
            });
        }

        $sort = $request->input('sort', 'date');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $sortable = ['date', 'amount', 'id'];
        $sort = in_array($sort, $sortable, true) ? $sort : 'date';

        $incomeRecords = $query->with('currency')
                              ->orderBy($sort, $direction)
                              ->orderBy('id', 'desc')
                              ->paginate(10)
                              ->withQueryString();

        $totalAmount = (float) $request->user()->incomeRecords()->sum('amount');

        return view('income.index', [
            'incomeRecords' => $incomeRecords,
            'totalAmount' => $totalAmount,
            'currencies' => $this->activeCurrencies(),
        ]);
    }

    /**
     * Show the form for creating a new income record.
     */
    public function create()
    {
        return view('income.create', [
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
        ]);
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
            'source' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        // Resolve the selected currency (only the user's own currencies are valid),
        // falling back to the system default currency from Settings. The code is
        // snapshotted so the original currency is preserved even if the system
        // default later changes.
        $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);

        $request->user()->incomeRecords()->create([
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'source' => $validated['source'] ?? null,
            'category' => $validated['category'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'currency_id' => $currency?->id,
            'currency_code' => $currency?->code ?? Currency::systemDefaultCode($request->user()->id),
        ]);

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
            'currencies' => $this->activeCurrencies(),
            'defaultCurrency' => Currency::defaultFor(Auth::id()),
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
            'source' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'currency_id' => ['nullable', Rule::exists('currencies', 'id')->where('user_id', $request->user()->id)],
        ]);

        $payload = [
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'source' => $validated['source'] ?? null,
            'category' => $validated['category'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        // Only change the record's currency when the user explicitly picks one.
        if ($request->filled('currency_id')) {
            $currency = $this->resolveCurrency($request->input('currency_id'), $request->user()->id);
            $payload['currency_id'] = $currency?->id;
            $payload['currency_code'] = $currency?->code;
        }

        $income->update($payload);

        return redirect()->route('income.index')->with('status', 'Income record updated successfully.');
    }

    /**
     * Active currencies belonging to the authenticated user (inactive ones are
     * hidden from new-record forms but remain visible on existing records).
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
