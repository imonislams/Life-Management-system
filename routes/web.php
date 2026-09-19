<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SavingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Money Management Overview
    Route::get('/money-management', [\App\Http\Controllers\MoneyManagementController::class, 'index'])->name('money-management.index');

    // Savings transactions (ledger) - declared before the goal routes so the
    // literal "transactions" segment is never captured as a goal id.
    Route::post('/savings/transactions', [SavingsController::class, 'storeTransaction'])->name('savings.transactions.store');

    // Savings goals (multiple per user) - CRUD. The {savingsGoal} parameter name
    // is scoped to SavingsGoal so it never collides with the personal Goal
    // module's {goal} parameter.
    Route::get('/savings/goals/create', [SavingsController::class, 'create'])->name('savings.goals.create');
    Route::post('/savings/goals', [SavingsController::class, 'store'])->name('savings.goals.store');
    Route::get('/savings/goals/{savingsGoal}/edit', [SavingsController::class, 'edit'])->name('savings.goals.edit');
    Route::put('/savings/goals/{savingsGoal}', [SavingsController::class, 'update'])->name('savings.goals.update');
    Route::delete('/savings/goals/{savingsGoal}', [SavingsController::class, 'destroy'])->name('savings.goals.destroy');
    Route::get('/savings/goals/{savingsGoal}', [SavingsController::class, 'show'])->name('savings.goals.show');

    // Edit / delete an existing savings transaction.
    Route::get('/savings/transactions/{transaction}/edit', [SavingsController::class, 'editTransaction'])->name('savings.transactions.edit');
    Route::put('/savings/transactions/{transaction}', [SavingsController::class, 'updateTransaction'])->name('savings.transactions.update');
    Route::delete('/savings/transactions/{transaction}', [SavingsController::class, 'destroyTransaction'])->name('savings.transactions.destroy');

    // Salary resource routes
    Route::resource('salary', SalaryController::class)->except(['show']);

    // Savings overview
    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');

    // Income resource routes
    Route::resource('income', IncomeController::class)->except(['show']);

    // Expense resource routes
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Recurring Transactions resource routes
    Route::resource('recurring-transactions', RecurringTransactionController::class)->except(['show']);

    // Daily Activities - a personal daily journal of what the user actually did.
    // (Replaces the former separate Daily Tasks and Daily Work Logs modules.)
    Route::resource('/daily-management/activities', \App\Http\Controllers\DailyActivityController::class)
        ->parameters(['activities' => 'dailyActivity'])
        ->names('daily-activities');

    // Habits routes
    Route::patch('/daily-management/habits/{habit}/toggle', [\App\Http\Controllers\HabitController::class, 'toggleCompletion'])->name('habits.toggle');
    Route::post('/daily-management/habits/{habit}/activities', [\App\Http\Controllers\HabitActivityController::class, 'store'])->name('habit-activities.store');
    Route::patch('/daily-management/habit-activities/{activity}/toggle', [\App\Http\Controllers\HabitActivityController::class, 'toggleCompletion'])->name('habit-activities.toggle');
    Route::put('/daily-management/habit-activities/{activity}', [\App\Http\Controllers\HabitActivityController::class, 'update'])->name('habit-activities.update');
    Route::delete('/daily-management/habit-activities/{activity}', [\App\Http\Controllers\HabitActivityController::class, 'destroy'])->name('habit-activities.destroy');
    Route::resource('/daily-management/habits', \App\Http\Controllers\HabitController::class)->names('habits');

    // Daily Routine routes (parameter name kept as 'routine' so route helper
    // names such as route('routine.edit', $item) work as expected).
    Route::patch('/daily-management/routine/{routine}/mark', [\App\Http\Controllers\RoutineOccurrenceController::class, 'mark'])->name('routine.mark');
    Route::get('/daily-management/routine/{routine}/occurrences', [\App\Http\Controllers\RoutineOccurrenceController::class, 'index'])->name('routine.occurrences');
    Route::patch('/daily-management/routine-occurrences/{occurrence}', [\App\Http\Controllers\RoutineOccurrenceController::class, 'update'])->name('routine-occurrences.update');
    Route::resource('/daily-management/routine', \App\Http\Controllers\RoutineItemController::class)
        ->parameters(['routine' => 'routine'])
        ->names('routine');

    // Personal Growth Routes
    Route::get('/personal-growth/progress', [\App\Http\Controllers\ProgressController::class, 'index'])->name('progress.index');

    Route::patch('/personal-growth/goals/{goal}/progress', [\App\Http\Controllers\GoalController::class, 'updateProgress'])->name('goals.progress');

    // Goal daily progress history (add / edit / delete a progress update).
    Route::post('/personal-growth/goals/{goal}/progress-updates', [\App\Http\Controllers\GoalProgressUpdateController::class, 'store'])->name('goal-progress.store');
    Route::put('/personal-growth/goal-progress/{progressUpdate}', [\App\Http\Controllers\GoalProgressUpdateController::class, 'update'])->name('goal-progress.update');
    Route::delete('/personal-growth/goal-progress/{progressUpdate}', [\App\Http\Controllers\GoalProgressUpdateController::class, 'destroy'])->name('goal-progress.destroy');

    Route::resource('/personal-growth/goals', \App\Http\Controllers\GoalController::class)->names('goals');

    // Important Dates Routes
    Route::get('/important-dates/calendar', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');

    Route::resource('/important-dates/events', \App\Http\Controllers\EventController::class)->names('events');

    // AI Assistant (Personal Workspace, 100% local). Every conversation is
    // scoped to the authenticated user; cross-user ids 404 via the route binding.
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/assistant', [\App\Http\Controllers\AIAssistantController::class, 'index'])->name('assistant');
        Route::post('/assistant/ask', [\App\Http\Controllers\AIAssistantController::class, 'ask'])->name('assistant.ask');
        Route::delete('/conversations/{conversation}', [\App\Http\Controllers\AIAssistantController::class, 'destroy'])->name('conversations.destroy');
    });

    // Settings Routes (per-user; ownership always resolved from the session user)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');

        Route::get('/general', [\App\Http\Controllers\SettingsController::class, 'general'])->name('general');
        Route::put('/general', [\App\Http\Controllers\SettingsController::class, 'updateGeneral'])->name('general.update');

        Route::get('/currency', [\App\Http\Controllers\SettingsController::class, 'currency'])->name('currency');
        Route::put('/currency', [\App\Http\Controllers\SettingsController::class, 'updateCurrency'])->name('currency.update');

        // Currency management (multi-currency CRUD, per user)
        Route::get('/currencies', [\App\Http\Controllers\CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/currencies/create', [\App\Http\Controllers\CurrencyController::class, 'create'])->name('currencies.create');
        Route::post('/currencies', [\App\Http\Controllers\CurrencyController::class, 'store'])->name('currencies.store');
        Route::post('/currencies/from-catalog', [\App\Http\Controllers\CurrencyController::class, 'addFromCatalog'])->name('currencies.catalog');
        Route::get('/currencies/{currency}/edit', [\App\Http\Controllers\CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/currencies/{currency}', [\App\Http\Controllers\CurrencyController::class, 'update'])->name('currencies.update');
        Route::patch('/currencies/{currency}/toggle', [\App\Http\Controllers\CurrencyController::class, 'toggle'])->name('currencies.toggle');
        Route::patch('/currencies/{currency}/default', [\App\Http\Controllers\CurrencyController::class, 'setDefault'])->name('currencies.default');
        Route::delete('/currencies/{currency}', [\App\Http\Controllers\CurrencyController::class, 'destroy'])->name('currencies.destroy');

        Route::get('/salary', [\App\Http\Controllers\SettingsController::class, 'salary'])->name('salary');
        Route::get('/savings', [\App\Http\Controllers\SettingsController::class, 'savings'])->name('savings');
        Route::get('/income', [\App\Http\Controllers\SettingsController::class, 'income'])->name('income');
        Route::get('/expense', [\App\Http\Controllers\SettingsController::class, 'expense'])->name('expense');
        Route::get('/recurring', [\App\Http\Controllers\SettingsController::class, 'recurring'])->name('recurring');
        Route::put('/finance', [\App\Http\Controllers\SettingsController::class, 'updateFinance'])->name('finance.update');

        Route::get('/notifications', [\App\Http\Controllers\SettingsController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [\App\Http\Controllers\SettingsController::class, 'updateNotifications'])->name('notifications.update');

        Route::get('/appearance', [\App\Http\Controllers\SettingsController::class, 'appearance'])->name('appearance');
        Route::put('/appearance', [\App\Http\Controllers\SettingsController::class, 'updateAppearance'])->name('appearance.update');

        Route::get('/profile', [\App\Http\Controllers\SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('profile.update');

        Route::get('/security', [\App\Http\Controllers\SettingsController::class, 'security'])->name('security');
        Route::put('/security/password', [\App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('password.update');

        // AI status (local AI health: Ollama, models, vector database).
        Route::get('/ai', [\App\Http\Controllers\SettingsController::class, 'ai'])->name('ai');
    });
});
