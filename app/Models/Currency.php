<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Currency extends Model
{
    use HasFactory;

    public const SYMBOL_POSITIONS = [
        'before' => 'Before amount',
        'after' => 'After amount',
    ];

    /**
     * Common world currencies offered as quick-add presets.
     *
     * This list is a convenience, not a restriction: any valid ISO-style code
     * can be added manually through the Add Currency form.
     *
     * @var array<int, array{code:string,name:string,symbol:string,country:string,precision:int}>
     */
    public const CATALOG = [
        ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => "\u{09F3}", 'country' => 'Bangladesh', 'precision' => 2],
        ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'country' => 'United States', 'precision' => 2],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => "\u{20AC}", 'country' => 'European Union', 'precision' => 2, 'thousands' => '.', 'decimal' => ',', 'position' => 'after'],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => "\u{00A3}", 'country' => 'United Kingdom', 'precision' => 2],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => "\u{20B9}", 'country' => 'India', 'precision' => 2],
        ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => "\u{20A8}", 'country' => 'Pakistan', 'precision' => 2],
        ['code' => 'NPR', 'name' => 'Nepalese Rupee', 'symbol' => "\u{0930}\u{0942}", 'country' => 'Nepal', 'precision' => 2],
        ['code' => 'LKR', 'name' => 'Sri Lankan Rupee', 'symbol' => 'Rs', 'country' => 'Sri Lanka', 'precision' => 2],
        ['code' => 'AED', 'name' => 'United Arab Emirates Dirham', 'symbol' => "\u{062F}.\u{0625}", 'country' => 'United Arab Emirates', 'precision' => 2],
        ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => "\u{0631}.\u{0633}", 'country' => 'Saudi Arabia', 'precision' => 2],
        ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => "\u{0631}.\u{0642}", 'country' => 'Qatar', 'precision' => 2],
        ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => "\u{062F}.\u{0643}", 'country' => 'Kuwait', 'precision' => 3],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => "\u{00A5}", 'country' => 'Japan', 'precision' => 0],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => "\u{00A5}", 'country' => 'China', 'precision' => 2],
        ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => "\u{20A9}", 'country' => 'South Korea', 'precision' => 0],
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => '$', 'country' => 'Canada', 'precision' => 2],
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => '$', 'country' => 'Australia', 'precision' => 2],
        ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => '$', 'country' => 'New Zealand', 'precision' => 2],
        ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'country' => 'Switzerland', 'precision' => 2],
        ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'country' => 'Sweden', 'precision' => 2],
        ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'country' => 'Norway', 'precision' => 2],
        ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'country' => 'Denmark', 'precision' => 2],
        ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => "\u{20BD}", 'country' => 'Russia', 'precision' => 2],
        ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => "\u{20BA}", 'country' => 'Turkey', 'precision' => 2],
        ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'country' => 'Brazil', 'precision' => 2],
        ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'country' => 'Mexico', 'precision' => 2],
        ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'country' => 'South Africa', 'precision' => 2],
        ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => "\u{20A6}", 'country' => 'Nigeria', 'precision' => 2],
        ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => "\u{20B5}", 'country' => 'Ghana', 'precision' => 2],
        ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'country' => 'Kenya', 'precision' => 2],
        ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E\u{00A3}', 'country' => 'Egypt', 'precision' => 2],
        ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => "\u{062F}.\u{0645}.", 'country' => 'Morocco', 'precision' => 2],
        ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => "\u{0E3F}", 'country' => 'Thailand', 'precision' => 2],
        ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'country' => 'Malaysia', 'precision' => 2],
        ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$', 'country' => 'Singapore', 'precision' => 2],
        ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'country' => 'Indonesia', 'precision' => 0],
        ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => "\u{20AB}", 'country' => 'Vietnam', 'precision' => 0],
        ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => "\u{20B1}", 'country' => 'Philippines', 'precision' => 2],
        ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => '$', 'country' => 'Hong Kong', 'precision' => 2],
        ['code' => 'TWD', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$', 'country' => 'Taiwan', 'precision' => 2],
        ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'z\u{0142}', 'country' => 'Poland', 'precision' => 2],
        ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'K\u{010D}', 'country' => 'Czechia', 'precision' => 2],
        ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'country' => 'Hungary', 'precision' => 0],
        ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia', 'symbol' => "\u{20B4}", 'country' => 'Ukraine', 'precision' => 2],
        ['code' => 'ILS', 'name' => 'Israeli New Shekel', 'symbol' => "\u{20AA}", 'country' => 'Israel', 'precision' => 2],
    ];

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'symbol',
        'country',
        'decimal_precision',
        'thousands_separator',
        'decimal_separator',
        'symbol_position',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'decimal_precision' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * The user's default currency, or the first active one, or null.
     */
    public static function defaultFor(int $userId): ?self
    {
        $default = static::ownedBy($userId)->where('is_default', true)->first();

        if ($default) {
            return $default;
        }

        return static::ownedBy($userId)->active()->orderBy('sort_order')->orderBy('code')->first();
    }

    /**
     * The system-wide default currency code for a user.
     *
     * The Settings -> Currency configuration is authoritative; the per-user
     * currency list is only a secondary fallback so legacy multi-currency users
     * are never broken.
     */
    public static function systemDefaultCode(int $userId): ?string
    {
        $settings = Setting::forUser($userId);

        if ($settings && $settings->currency_code) {
            return (string) $settings->currency_code;
        }

        return static::defaultFor($userId)?->code;
    }

    /**
     * Make this currency the single default for the owning user.
     *
     * Runs in a transaction so a user can never end up with two defaults.
     * Historical financial records are never touched here.
     */
    public function makeDefault(): void
    {
        DB::transaction(function () {
            static::ownedBy($this->user_id)->where('id', '!=', $this->id)->update(['is_default' => false]);
            $this->forceFill(['is_default' => true, 'is_active' => true])->save();
        });
    }

    /**
     * Whether any financial record references this currency.
     */
    public function isUsedInFinancialRecords(): bool
    {
        return $this->incomeRecords()->exists()
            || $this->expenseRecords()->exists()
            || $this->salaries()->exists()
            || $this->savingsGoals()->exists()
            || $this->recurringTransactions()->exists();
    }

    /**
     * Number of financial records referencing this currency.
     */
    public function usageCount(): int
    {
        return $this->incomeRecords()->count()
            + $this->expenseRecords()->count()
            + $this->salaries()->count()
            + $this->savingsGoals()->count()
            + $this->recurringTransactions()->count();
    }

    /**
     * Human-readable formatting preview, e.g. "৳ 1,234.50".
     */
    public function preview($amount = 1234.5): string
    {
        $number = number_format(
            (float) $amount,
            $this->decimal_precision,
            $this->decimal_separator,
            $this->thousands_separator
        );

        $sep = chr(32);

        return $this->symbol_position === 'after'
            ? $number . $sep . $this->symbol
            : $this->symbol . $sep . $number;
    }

    // -----------------------------------------------------------------
    // Relationships back to financial records
    // -----------------------------------------------------------------

    public function incomeRecords()
    {
        return $this->hasMany(IncomeRecord::class);
    }

    public function expenseRecords()
    {
        return $this->hasMany(ExpenseRecord::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function recurringTransactions()
    {
        return $this->hasMany(RecurringTransaction::class);
    }
}
