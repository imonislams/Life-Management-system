<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Resolves the active currency display configuration.
 *
 * User settings (when present) take precedence; otherwise the application-wide
 * config defaults are used. This keeps the existing Money Management output
 * unchanged while allowing per-user currency customisation.
 */
class CurrencyConfig
{
    /**
     * Well-known symbols for common currency codes, used when a user changes the
     * code but does not supply a custom symbol.
     */
    public const SYMBOLS = [
        'BDT' => "\u{09F3}",
        'USD' => '$',
        'EUR' => "\u{20AC}",
        'GBP' => "\u{00A3}",
        'INR' => "\u{20B9}",
        'PKR' => "\u{20A8}",
        'JPY' => "\u{00A5}",
        'CNY' => "\u{00A5}",
        'AUD' => 'A$',
        'CAD' => 'C$',
        'AED' => "\u{062F}.\u{0625}",
        'SAR' => "\u{FDFC}",
        'MYR' => 'RM',
        'SGD' => 'S$',
        'TRY' => "\u{20BA}",
    ];

    /**
     * Resolve the currency configuration for a user id (nullable = app default).
     *
     * @return array<string, mixed>
     */
    public static function resolve(?int $userId): array
    {
        $defaults = [
            'symbol' => (string) config('life.currency.symbol', "\u{09F3}"),
            'decimals' => (int) config('life.currency.decimals', 2),
            'thousands_separator' => (string) config('life.currency.thousands_separator', ','),
            'decimal_separator' => (string) config('life.currency.decimal_separator', '.'),
            'position' => (string) config('life.currency.position', 'before'),
            'suffix' => (string) config('life.currency.suffix', 'TK'),
        ];

        if (! $userId) {
            return $defaults;
        }

        $settings = Setting::where('user_id', $userId)->first();

        if (! $settings) {
            return $defaults;
        }

        return [
            'symbol' => $settings->currency_symbol ?: $defaults['symbol'],
            'decimals' => (int) $settings->currency_decimals,
            'thousands_separator' => $settings->currency_thousands_separator !== null
                ? (string) $settings->currency_thousands_separator
                : $defaults['thousands_separator'],
            'decimal_separator' => $settings->currency_decimal_separator !== null
                ? (string) $settings->currency_decimal_separator
                : $defaults['decimal_separator'],
            'position' => $settings->currency_position ?: $defaults['position'],
            'suffix' => $settings->currency_active ? (string) $settings->currency_suffix : '',
        ];
    }

    /**
     * The user's Settings -> Currency configuration expressed as a code/symbol
     * pair. This is the system-wide default currency for the whole workspace.
     *
     * @return array{code:string,symbol:string}
     */
    public static function defaultCurrency(?int $userId): array
    {
        if ($userId) {
            $settings = \App\Models\Setting::forUser($userId);

            if ($settings && $settings->currency_code) {
                return [
                    'code' => (string) $settings->currency_code,
                    'symbol' => (string) ($settings->currency_symbol ?: ''),
                ];
            }
        }

        return [
            'code' => (string) config('life.currency.code', 'BDT'),
            'symbol' => (string) config('life.currency.symbol', "\u{09F3}"),
        ];
    }

    /**
     * Format an amount for a specific user using their saved currency settings.
     */
    public static function format(?int $userId, $amount, ?bool $withSuffix = null): string
    {
        $config = self::resolve($userId);

        $amount = (float) $amount;

        $number = number_format(
            $amount,
            $config['decimals'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        $sep = chr(32);

        $formatted = $config['position'] === 'after'
            ? $number . $sep . $config['symbol']
            : $config['symbol'] . $sep . $number;

        $suffix = $withSuffix === false ? '' : $config['suffix'];

        return $suffix !== '' ? $formatted . $sep . $suffix : $formatted;
    }
}

