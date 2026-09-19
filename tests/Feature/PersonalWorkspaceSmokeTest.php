<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Read-only smoke test: authenticates a user and exercises every Personal
 * Workspace GET route to catch Blade / controller / query errors.
 *
 * Uses DatabaseTransactions so the real MySQL data set is never modified.
 */
class PersonalWorkspaceSmokeTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeUser(): User
    {
        $user = User::factory()->create();

        // Give the user a default currency so currency-dependent views render.
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

    public function test_all_personal_workspace_pages_load(): void
    {
        $user = $this->makeUser();

        $routes = [
            '/dashboard',
            '/money-management',
            '/income',
            '/income/create',
            '/salary',
            '/salary/create',
            '/expenses',
            '/expenses/create',
            '/savings',
            '/savings/goals/create',
            '/recurring-transactions',
            '/recurring-transactions/create',
            '/daily-management/activities',
            '/daily-management/activities/create',
            '/daily-management/routine',
            '/daily-management/routine/create',
            '/daily-management/habits',
            '/daily-management/habits/create',
            '/personal-growth/goals',
            '/personal-growth/goals/create',
            '/personal-growth/progress',
            '/settings/currency',
            '/important-dates/calendar',
            '/important-dates/events',
            '/important-dates/events/create',
            '/settings',
            '/settings/general',
            '/settings/currency',
            '/settings/currencies',
            '/settings/currencies/create',
            '/settings/salary',
            '/settings/savings',
            '/settings/income',
            '/settings/expense',
            '/settings/recurring',
            '/settings/notifications',
            '/settings/appearance',
            '/settings/profile',
            '/settings/security',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302], true),
                "Route {$route} returned HTTP {$response->getStatusCode()}"
            );
        }
    }

    public function test_user_cannot_view_another_users_savings_goal(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser();

        $goal = $owner->savingsGoals()->create([
            'name' => 'Owner Goal',
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Scoped route binding must 404 for a different user.
        $this->actingAs($intruder)
            ->get("/savings/goals/{$goal->id}")
            ->assertNotFound();
    }
}
