<?php

namespace App\Providers;

use App\Models\AiConversation;
use App\Models\Currency;
use App\Models\DailyActivity;
use App\Models\Event;
use App\Models\Goal;
use App\Models\GoalProgressUpdate;
use App\Models\Habit;
use App\Models\HabitActivity;
use App\Models\RoutineItem;
use App\Models\RoutineOccurrence;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Policies\AiConversationPolicy;
use App\Policies\CurrencyPolicy;
use App\Policies\DailyActivityPolicy;
use App\Policies\EventPolicy;
use App\Policies\GoalPolicy;
use App\Policies\GoalProgressUpdatePolicy;
use App\Policies\HabitActivityPolicy;
use App\Policies\HabitPolicy;
use App\Policies\RoutineItemPolicy;
use App\Policies\RoutineOccurrencePolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\SavingsTransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AiConversation::class, AiConversationPolicy::class);
        Gate::policy(Currency::class, CurrencyPolicy::class);
        Gate::policy(Habit::class, HabitPolicy::class);
        Gate::policy(HabitActivity::class, HabitActivityPolicy::class);
        Gate::policy(RoutineItem::class, RoutineItemPolicy::class);
        Gate::policy(RoutineOccurrence::class, RoutineOccurrencePolicy::class);
        Gate::policy(Goal::class, GoalPolicy::class);
        Gate::policy(GoalProgressUpdate::class, GoalProgressUpdatePolicy::class);
        Gate::policy(DailyActivity::class, DailyActivityPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(SavingsGoal::class, SavingsGoalPolicy::class);
        Gate::policy(SavingsTransaction::class, SavingsTransactionPolicy::class);

        // Scoped route bindings: a {goal}, {transaction}, {workLog}, {activity}
        // or {occurrence} can only ever resolve to a record owned by the current
        // user, so cross-user access 404s before it reaches the controller.
        Route::bind('savingsGoal', function ($value) {
            return SavingsGoal::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('transaction', function ($value) {
            return SavingsTransaction::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('activity', function ($value) {
            return HabitActivity::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('occurrence', function ($value) {
            return RoutineOccurrence::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('dailyActivity', function ($value) {
            return DailyActivity::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('progressUpdate', function ($value) {
            return GoalProgressUpdate::where('user_id', auth()->id())->findOrFail($value);
        });

        // AI conversations can only ever resolve to the current user's own rows,
        // so a cross-user id 404s before it reaches the controller.
        Route::bind('conversation', function ($value) {
            return AiConversation::where('user_id', auth()->id())->findOrFail($value);
        });
    }
}
