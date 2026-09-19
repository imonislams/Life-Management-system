<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Write-path smoke tests for the Personal Workspace CRUD flows.
 * Runs inside a transaction, so the real MySQL data set is untouched.
 */
class PersonalWorkspaceWriteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // The feature tests post forms directly, so CSRF verification is skipped.
        // Other middleware (auth, route-model binding) stays enabled so the test
        // exercises the real request lifecycle.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    protected function makeUser(): User
    {
        $user = User::factory()->create();

        Currency::create([
            'user_id' => $user->id,
            'name' => 'Bangladeshi Taka',
            'code' => 'BDT',
            'symbol' => "\u{09F3}",
            'decimal_precision' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            'symbol_position' => 'before',
            'is_active' => true,
            'is_default' => true,
        ]);

        return $user;
    }

    public function test_savings_goal_and_transaction_flow(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/savings/goals', [
            'name' => 'New Laptop',
            'target_amount' => 100000,
            'current_amount' => 5000,
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $goal = $user->savingsGoals()->firstOrFail();
        $this->assertEquals(5000.0, (float) $goal->current_amount);

        // A deposit increases the balance and writes a ledger row.
        $this->actingAs($user)->post('/savings/transactions', [
            'savings_goal_id' => $goal->id,
            'type' => 'deposit',
            'amount' => 2000,
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $goal->refresh();
        $this->assertEquals(7000.0, (float) $goal->current_amount);
        $this->assertEquals(2, $goal->transactions()->count());

        // A withdrawal beyond the balance is rejected.
        $this->actingAs($user)->post('/savings/transactions', [
            'savings_goal_id' => $goal->id,
            'type' => 'withdrawal',
            'amount' => 9999,
            'date' => now()->toDateString(),
        ])->assertSessionHasErrors('amount');

        $goal->refresh();
        $this->assertEquals(7000.0, (float) $goal->current_amount);
    }

    public function test_daily_activity_create_derives_duration(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/daily-management/activities', [
            'title' => 'Studied Laravel for 2 hours',
            'description' => 'Worked on Eloquent relationships',
            'activity_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'category' => 'Study',
            'status' => 'completed',
        ])->assertRedirect();

        $activity = $user->dailyActivities()->firstOrFail();
        $this->assertEquals('Study', $activity->category);
        $this->assertEquals(120, $activity->duration_minutes);
    }

    public function test_goal_progress_update_drives_measurable_progress(): void
    {
        $user = $this->makeUser();

        // Create a measurable goal with a numeric target.
        $this->actingAs($user)->post('/personal-growth/goals', [
            'title' => 'Read 100 pages',
            'priority' => 'medium',
            'status' => 'in_progress',
            'progress_type' => 'measurable',
            'target_amount' => 100,
        ])->assertSessionHasNoErrors();

        $goal = $user->goals()->firstOrFail();
        $this->assertEquals(0.0, (float) $goal->current_amount);

        // Two progress updates build the current amount from real stored data.
        $this->actingAs($user)->post("/personal-growth/goals/{$goal->id}/progress-updates", [
            'date' => now()->toDateString(),
            'description' => 'Read chapter 1',
            'progress_value' => 30,
            'time_spent_minutes' => 60,
        ])->assertRedirect();

        $this->actingAs($user)->post("/personal-growth/goals/{$goal->id}/progress-updates", [
            'date' => now()->toDateString(),
            'description' => 'Read chapter 2',
            'progress_value' => 45,
        ])->assertRedirect();

        $goal->refresh();
        $this->assertEquals(75.0, (float) $goal->current_amount);
        $this->assertEquals(75.0, $goal->progressPercentage());
        $this->assertEquals(2, $goal->progressUpdates()->count());
    }

    public function test_habit_with_activities_and_completion(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/daily-management/habits', [
            'title' => 'Daily Prayer',
            'frequency' => 'daily',
            'status' => 'active',
            'activities' => [
                ['name' => 'Fajr'],
                ['name' => 'Dhuhr'],
                ['name' => 'Asr'],
            ],
        ])->assertRedirect();

        $habit = $user->habits()->firstOrFail();
        $this->assertEquals(3, $habit->activities()->count());

        $activity = $habit->activities()->first();
        $this->actingAs($user)
            ->patch("/daily-management/habit-activities/{$activity->id}/toggle")
            ->assertRedirect();

        $this->assertEquals(1, $habit->completions()->count());

        // Toggling again removes the completion (no duplicates).
        $this->actingAs($user)
            ->patch("/daily-management/habit-activities/{$activity->id}/toggle")
            ->assertRedirect();

        $this->assertEquals(0, $habit->completions()->count());
    }

    public function test_routine_recurrence_and_occurrence(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/daily-management/routine', [
            'title' => 'Study English',
            'recurrence_type' => 'custom_days',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'status' => 'active',
            'days_of_week' => [1, 3, 5],
        ])->assertRedirect();

        $routine = $user->routines()->firstOrFail();
        $this->assertEquals([1, 3, 5], $routine->dayOfWeekList());

        // Marking today's occurrence completes it (or creates it).
        $this->actingAs($user)->patch("/daily-management/routine/{$routine->id}/mark", [
            'occurrence_date' => now()->toDateString(),
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertEquals(1, $routine->occurrences()->where('status', 'completed')->count());
    }

    public function test_goal_with_new_fields(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/personal-growth/goals', [
            'title' => 'Run a marathon',
            'priority' => 'high',
            'status' => 'in_progress',
            'progress_type' => 'qualitative',
            'progress' => 30,
            'target_value' => '42km',
            'notes' => 'Train weekly',
        ])->assertSessionHasNoErrors();

        $goal = $user->goals()->firstOrFail();
        $this->assertEquals('42km', $goal->target_value);
        $this->assertEquals(30, $goal->progress);
    }
}
