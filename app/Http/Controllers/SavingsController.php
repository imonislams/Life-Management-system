<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingsGoalRequest;
use App\Http\Requests\SavingsTransactionRequest;
use App\Models\Currency;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SavingsController extends Controller
{
    /**
     * Savings overview: all of the user's goals, a per-currency summary and the
     * recent transaction ledger. Every query is scoped to the authenticated user.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $goals = $user->savingsGoals()
            ->with('currency')
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'paused' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->get();

        $goalId = $request->get('goal');

        $transactions = $user->savingsTransactions()
            ->with(['goal', 'currency'])
            ->when($goalId, fn ($q) => $q->where('savings_goal_id', $goalId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalDeposits = (float) $user->savingsTransactions()->where('type', 'deposit')->sum('amount');
        $totalWithdrawals = (float) $user->savingsTransactions()->where('type', 'withdrawal')->sum('amount');

        // Current balance held by the user's goals, grouped per currency so
        // different currencies are never added together.
        $totalsByCurrency = $user->savingsGoals()
            ->select('currency_code', DB::raw('SUM(current_amount) as total'), DB::raw('SUM(target_amount) as target'))
            ->groupBy('currency_code')
            ->get()
            ->map(fn ($row) => [
                'code' => $row->currency_code ?: '\u{2014}',
                'total' => (float) $row->total,
                'target' => (float) $row->target,
            ]);

        return view('savings.index', [
            'goals' => $goals,
            'transactions' => $transactions,
            'totalsByCurrency' => $totalsByCurrency,
            'totalDeposits' => $totalDeposits,
            'totalWithdrawals' => $totalWithdrawals,
            'currencies' => Currency::ownedBy($user->id)->active()->orderByDesc('is_default')->orderBy('code')->get(),
            'defaultCurrency' => Currency::defaultFor($user->id),
            'selectedGoalId' => $goalId,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('savings.goals.create', [
            'currencies' => Currency::ownedBy($user->id)->active()->orderByDesc('is_default')->orderBy('code')->get(),
            'defaultCurrency' => Currency::defaultFor($user->id),
        ]);
    }

    /**
     * Create a new savings goal/account.
     */
    public function store(SavingsGoalRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $currency = $this->resolveCurrency($validated['currency_id'] ?? null, $user->id);

        $goal = $user->savingsGoals()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'current_amount' => 0,
            'start_date' => $validated['start_date'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'],
            'currency_id' => $currency?->id,
            'currency_code' => $currency?->code ?? Currency::systemDefaultCode($user->id),
        ]);

        // Seed the opening balance as a deposit so the ledger matches the balance.
        $opening = (float) ($validated['current_amount'] ?? 0);
        if ($opening > 0) {
            $this->recordTransaction($goal, [
                'type' => 'deposit',
                'amount' => $opening,
                'date' => $validated['start_date'] ?? Carbon::today()->toDateString(),
                'note' => 'Opening balance',
            ], $user->id);
        }

        return redirect()->route('savings.goals.show', $goal)->with('status', 'Savings goal created successfully.');
    }

    public function show(Request $request, SavingsGoal $savingsGoal): View
    {
        $goal = $savingsGoal;
        $this->authorize('view', $goal);

        $user = $request->user();

        $transactions = $goal->transactions()
            ->with('currency')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('savings.goals.show', [
            'goal' => $goal,
            'transactions' => $transactions,
            'totalDeposits' => (float) $goal->transactions()->where('type', 'deposit')->sum('amount'),
            'totalWithdrawals' => (float) $goal->transactions()->where('type', 'withdrawal')->sum('amount'),
            'currencies' => Currency::ownedBy($user->id)->active()->orderByDesc('is_default')->orderBy('code')->get(),
            'defaultCurrency' => Currency::defaultFor($user->id),
        ]);
    }

    public function edit(Request $request, SavingsGoal $savingsGoal): View
    {
        $goal = $savingsGoal;
        $this->authorize('update', $goal);

        $user = $request->user();

        return view('savings.goals.edit', [
            'goal' => $goal,
            'currencies' => Currency::ownedBy($user->id)->active()->orderByDesc('is_default')->orderBy('code')->get(),
            'defaultCurrency' => Currency::defaultFor($user->id),
        ]);
    }

    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        $goal = $savingsGoal;
        $this->authorize('update', $goal);

        $user = $request->user();
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_amount' => $validated['target_amount'],
            'start_date' => $validated['start_date'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status' => $validated['status'],
        ];

        // Changing the currency only affects future records on this goal; the
        // historical transactions keep their own currency snapshot.
        if ($request->filled('currency_id')) {
            $currency = $this->resolveCurrency($validated['currency_id'], $user->id);
            $payload['currency_id'] = $currency?->id;
            $payload['currency_code'] = $currency?->code;
        }

        $goal->update($payload);

        return redirect()->route('savings.goals.show', $goal)->with('status', 'Savings goal updated successfully.');
    }

    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        $goal = $savingsGoal;
        $this->authorize('delete', $goal);

        // Transactions cascade with the goal (see the savings_transactions FK).
        $goal->delete();

        return redirect()->route('savings.index')->with('status', 'Savings goal deleted successfully.');
    }

    /**
     * Store a deposit or withdrawal against a goal.
     */
    public function storeTransaction(SavingsTransactionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $goal = $user->savingsGoals()->findOrFail($validated['savings_goal_id']);

        // A withdrawal must not push the balance below zero.
        if ($validated['type'] === 'withdrawal'
            && (float) $validated['amount'] > (float) $goal->current_amount) {
            return back()
                ->withErrors(['amount' => 'Withdrawal exceeds the current savings balance.'])
                ->withInput();
        }

        $this->recordTransaction($goal, $validated, $user->id);

        $label = $validated['type'] === 'withdrawal' ? 'Withdrawal' : 'Deposit';

        return redirect()->back()->with('status', $label . ' recorded successfully.');
    }

    public function editTransaction(Request $request, SavingsTransaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $user = $request->user();

        return view('savings.transactions.edit', [
            'transaction' => $transaction,
            'goals' => $user->savingsGoals()->orderBy('name')->get(),
            'currencies' => Currency::ownedBy($user->id)->active()->orderByDesc('is_default')->orderBy('code')->get(),
            'defaultCurrency' => Currency::defaultFor($user->id),
        ]);
    }

    public function updateTransaction(SavingsTransactionRequest $request, SavingsTransaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $transaction, $user) {
            // Reverse the old effect, then apply the new one, keeping the goal
            // balance consistent with the full ledger.
            $oldGoal = $transaction->goal()->first();
            $newGoal = $user->savingsGoals()->findOrFail($validated['savings_goal_id']);

            if ($oldGoal) {
                $this->recalculateBalance($oldGoal, -1 * $transaction->signedAmount());
            }

            $currency = $this->resolveCurrency($validated['currency_id'] ?? null, $user->id);

            $transaction->update([
                'savings_goal_id' => $newGoal->id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'note' => $validated['note'] ?? null,
                'currency_id' => $currency?->id,
                'currency_code' => $currency?->code,
            ]);

            $this->recalculateBalance($newGoal, $transaction->fresh()->signedAmount());
        });

        return redirect()->route('savings.index')->with('status', 'Transaction updated successfully.');
    }

    public function destroyTransaction(SavingsTransaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction) {
            $goal = $transaction->goal()->first();

            if ($goal) {
                $this->recalculateBalance($goal, -1 * $transaction->signedAmount());
            }

            $transaction->delete();
        });

        return redirect()->back()->with('status', 'Transaction deleted successfully.');
    }

    /**
     * Persist a transaction and adjust the goal balance atomically.
     */
    protected function recordTransaction(SavingsGoal $goal, array $data, int $userId): SavingsTransaction
    {
        return DB::transaction(function () use ($goal, $data, $userId) {
            $currency = $this->resolveCurrency($data['currency_id'] ?? null, $userId);

            $transaction = $goal->transactions()->create([
                'user_id' => $userId,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
                'currency_id' => $currency?->id ?? $goal->currency_id,
                'currency_code' => $currency?->code ?? $goal->currency_code,
            ]);

            $this->recalculateBalance($goal, $transaction->signedAmount());

            return $transaction;
        });
    }

    /**
     * Apply a signed delta to a goal's cached balance and auto-complete it when
     * the target is reached.
     */
    protected function recalculateBalance(SavingsGoal $goal, float $delta): void
    {
        $newBalance = max(0, (float) $goal->current_amount + $delta);

        $status = $goal->status;
        if ($status === 'active' && (float) $goal->target_amount > 0 && $newBalance >= (float) $goal->target_amount) {
            $status = 'completed';
        }

        $goal->forceFill([
            'current_amount' => $newBalance,
            'status' => $status,
        ])->save();
    }

    /**
     * Resolve a submitted currency id against the user's own active currencies,
     * falling back to their default currency. Never trusts the raw input.
     */
    protected function resolveCurrency($currencyId, int $userId): ?Currency
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
