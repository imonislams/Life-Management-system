<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    /**
     * Currency list with search, active/inactive filter and a formatting preview.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->currencies()->withCount([
            'incomeRecords',
            'expenseRecords',
            'salaries',
            'savingsGoals',
            'recurringTransactions',
        ]);

        // Search across name, code and country.
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%')
                    ->orWhere('symbol', 'like', '%' . $search . '%');
            });
        }

        // Active / inactive filter.
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $currencies = $query
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        $defaultCurrency = Currency::defaultFor($user->id);

        $totalCurrencies = $user->currencies()->count();
        $activeCurrencies = $user->currencies()->where('is_active', true)->count();
        $inactiveCurrencies = $user->currencies()->where('is_active', false)->count();

        // Suggested presets the user has not added yet.
        $existingCodes = $user->currencies()->pluck('code')->all();
        $catalogSuggestions = collect(Currency::CATALOG)
            ->reject(fn ($item) => in_array($item['code'], $existingCodes, true))
            ->values();

        return view('settings.currencies.index', compact(
            'currencies',
            'defaultCurrency',
            'totalCurrencies',
            'activeCurrencies',
            'inactiveCurrencies',
            'catalogSuggestions'
        ));
    }

    /**
     * Show the Add Currency form.
     */
    public function create()
    {
        $existingCodes = Auth::user()->currencies()->pluck('code')->all();

        $catalogSuggestions = collect(Currency::CATALOG)
            ->reject(fn ($item) => in_array($item['code'], $existingCodes, true))
            ->values();

        return view('settings.currencies.create', compact('catalogSuggestions'));
    }

    /**
     * Store a new currency for the authenticated user.
     */
    public function store(CurrencyRequest $request)
    {
        $data = $request->safe()->only([
            'name', 'code', 'symbol', 'country', 'decimal_precision',
            'thousands_separator', 'decimal_separator', 'symbol_position',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();

            // The very first currency a user adds automatically becomes the default.
            $isFirst = ! $user->currencies()->exists();

            $currency = $user->currencies()->create($data + [
                'is_default' => false,
                'sort_order' => (int) $user->currencies()->max('sort_order') + 1,
            ]);

            if ($isFirst || $request->boolean('is_default')) {
                $currency->makeDefault();
            }
        });

        return redirect()->route('settings.currencies.index')
            ->with('status', 'Currency added successfully.');
    }

    /**
     * Show the Edit Currency form.
     */
    public function edit(Currency $currency)
    {
        $this->authorize('update', $currency);

        return view('settings.currencies.edit', compact('currency'));
    }

    /**
     * Update a currency. Historical financial records are never touched.
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        $this->authorize('update', $currency);

        $data = $request->safe()->only([
            'name', 'code', 'symbol', 'country', 'decimal_precision',
            'thousands_separator', 'decimal_separator', 'symbol_position',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // The default currency must stay active.
        if ($currency->is_default) {
            $data['is_active'] = true;
        }

        $currency->update($data);

        if ($request->boolean('is_default')) {
            $currency->makeDefault();
        }

        return redirect()->route('settings.currencies.index')
            ->with('status', 'Currency updated successfully.');
    }

    /**
     * Activate or deactivate a currency.
     *
     * A default currency cannot be deactivated; the user must promote another
     * currency to default first.
     */
    public function toggle(Currency $currency)
    {
        $this->authorize('toggle', $currency);

        if ($currency->is_active && $currency->is_default) {
            return redirect()->back()->withErrors([
                'currency' => 'The default currency cannot be deactivated. Set another currency as default first.',
            ]);
        }

        $currency->update(['is_active' => ! $currency->is_active]);

        $state = $currency->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('status', sprintf('%s %s successfully.', $currency->code, $state));
    }

    /**
     * Promote a currency to be the single default for this user.
     */
    public function setDefault(Currency $currency)
    {
        $this->authorize('makeDefault', $currency);

        $currency->makeDefault();

        return redirect()->back()->with('status', sprintf('%s is now your default currency.', $currency->code));
    }

    /**
     * Delete a currency only when no financial record references it.
     */
    public function destroy(Currency $currency)
    {
        $this->authorize('delete', $currency);

        $usageCount = $currency->usageCount();

        if ($usageCount > 0) {
            return redirect()->back()->withErrors([
                'currency' => sprintf(
                    'This currency is used by %d financial record(s) and cannot be deleted. Deactivate it instead to keep existing records intact.',
                    $usageCount
                ),
            ]);
        }

        if ($currency->is_default) {
            return redirect()->back()->withErrors([
                'currency' => 'The default currency cannot be deleted. Set another currency as default first.',
            ]);
        }

        $code = $currency->code;
        $currency->delete();

        return redirect()->route('settings.currencies.index')
            ->with('status', sprintf('%s deleted successfully.', $code));
    }

    /**
     * Add a currency straight from the built-in world catalog.
     */
    public function addFromCatalog(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:8'],
        ]);

        $code = strtoupper(trim($validated['code']));

        $entry = collect(Currency::CATALOG)->firstWhere('code', $code);

        if (! $entry) {
            return redirect()->back()->withErrors(['currency' => 'Unknown catalog currency.']);
        }

        $user = $request->user();

        if ($user->currencies()->where('code', $code)->exists()) {
            return redirect()->back()->withErrors(['currency' => sprintf('You already have %s.', $code)]);
        }

        DB::transaction(function () use ($user, $entry) {
            $isFirst = ! $user->currencies()->exists();

            $currency = $user->currencies()->create([
                'name' => $entry['name'],
                'code' => $entry['code'],
                'symbol' => $entry['symbol'],
                'country' => $entry['country'],
                'decimal_precision' => $entry['precision'],
                'thousands_separator' => $entry['thousands'] ?? ',',
                'decimal_separator' => $entry['decimal'] ?? '.',
                'symbol_position' => $entry['position'] ?? 'before',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => (int) $user->currencies()->max('sort_order') + 1,
            ]);

            if ($isFirst) {
                $currency->makeDefault();
            }
        });

        return redirect()->route('settings.currencies.index')
            ->with('status', sprintf('%s - %s added successfully.', $entry['code'], $entry['name']));
    }
}
