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

    // Salary resource routes
    Route::resource('salary', SalaryController::class)->except(['show']);

    // Savings routes
    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
    Route::post('/savings', [SavingsController::class, 'storeOrUpdate'])->name('savings.store');
    Route::post('/savings/add-money', [SavingsController::class, 'addMoney'])->name('savings.add-money');

    // Income resource routes
    Route::resource('income', IncomeController::class)->except(['show']);

    // Expense resource routes
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Recurring Transactions resource routes
    Route::resource('recurring-transactions', RecurringTransactionController::class)->except(['show']);

    // Daily Management Routes (Phase 1 Placeholders)
    Route::get('/daily-management/tasks', function () {
        return view('daily-management.tasks');
    })->name('tasks.index');

    Route::get('/daily-management/habits', function () {
        return view('daily-management.habits');
    })->name('habits.index');

    Route::get('/daily-management/routine', function () {
        return view('daily-management.routine');
    })->name('routine.index');
});
