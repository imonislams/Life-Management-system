<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Setting;
use App\Models\User;
use App\Support\CurrencyConfig;
use App\Support\MoneyFormatter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Verifies that the currency chosen in Settings -> Currency becomes the
 * system-wide default used by the central formatter everywhere.
 */
class CurrencyCentralizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    protected function makeUser(): User
    {
        $user = User::factory()->create();

        Setting::forUser($user->id);

        return $user;
    }

    public function test_settings_currency_drives_system_formatting(): void
    {
        $user = $this->makeUser();

        // Switch the system currency to USD via the Settings -> Currency form.
        $this->actingAs($user)->put('/settings/currency', [
            'currency_name' => 'US Dollar',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_decimals' => 2,
            'currency_thousands_separator' => ',',
            'currency_decimal_separator' => '.',
            'currency_position' => 'before',
            'currency_suffix' => '',
            'currency_active' => 1,
        ])->assertRedirect();

        $user->refresh();
        $this->assertEquals('USD', $user->settings()->currency_code);

        // The central formatters now render USD.
        $this->assertStringContainsString('$', MoneyFormatter::format(1500, null, $user->id));
        $this->assertStringContainsString('1,500.00', CurrencyConfig::format($user->id, 1500));

        // Switch to BDT and confirm the formatting follows immediately.
        $this->actingAs($user)->put('/settings/currency', [
            'currency_name' => 'Bangladeshi Taka',
            'currency_code' => 'BDT',
            'currency_symbol' => "\u{09F3}",
            'currency_decimals' => 2,
            'currency_thousands_separator' => ',',
            'currency_decimal_separator' => '.',
            'currency_position' => 'before',
            'currency_suffix' => '',
            'currency_active' => 1,
        ])->assertRedirect();

        $this->assertStringContainsString("\u{09F3}", MoneyFormatter::format(1500, null, $user->id));
    }

    public function test_new_financial_record_snapshots_the_system_currency(): void
    {
        $user = $this->makeUser();

        Setting::forUser($user->id)->update([
            'currency_name' => 'US Dollar',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
        ]);

        $this->actingAs($user)->post('/income', [
            'amount' => 500,
            'date' => now()->toDateString(),
            'description' => 'Test income',
        ])->assertSessionHasNoErrors();

        // No explicit currency chosen, so the record snapshots the Settings code.
        $this->assertEquals('USD', $user->incomeRecords()->first()->currency_code);
    }

    public function test_per_record_currency_is_preserved_over_the_default(): void
    {
        $user = $this->makeUser();

        Setting::forUser($user->id)->update([
            'currency_code' => 'USD',
            'currency_symbol' => '$',
        ]);

        $eur = Currency::create([
            'user_id' => $user->id,
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => "\u{20AC}",
            'decimal_precision' => 2,
            'thousands_separator' => '.',
            'decimal_separator' => ',',
            'symbol_position' => 'after',
            'is_active' => true,
            'is_default' => false,
        ]);

        // A record explicitly denominated in EUR keeps EUR formatting even though
        // the system default is USD (no exchange-rate conversion happens).
        $formatted = MoneyFormatter::format(1500, $eur, $user->id);
        $this->assertStringContainsString("\u{20AC}", $formatted);
        $this->assertStringContainsString('1.500,00', $formatted);
    }
}
