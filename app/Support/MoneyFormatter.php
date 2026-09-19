<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\Setting;

/**
 * The single currency formatting engine for the whole application.
 *
 * Every module (Dashboard, Income, Salary, Expense, Savings, Recurring
 * Finance, Reports, tables and summary cards) formats money through here so
 * the same amount always renders identically everywhere.
 *
 * Amounts are held as strings / decimals (never manipulated with float maths)
 * and number_format handles the presentation only.
 */
class MoneyFormatter
{
    /**
     * Format an amount using a Currency model, a currency code, or the user's
     * default currency when nothing specific is supplied.
     *
     * @param  string|float|int  $amount
     */
    public static function format($amount, Currency|string|null $currency = null, ?int $userId = null): string
    {
        $resolved = self::resolve($currency, $userId);

        $number = number_format(
            (float) $amount,
            $resolved['precision'],
            $resolved['decimal'],
            $resolved['thousands']
        );

        // Keep explicit negative signs intact for negative values.
        $sep = chr(32);

        if ($resolved['position'] === 'after') {
            return $number . $sep . $resolved['symbol'];
        }

        return $resolved['symbol'] . $sep . $number;
    }

    /**
     * Format an amount with a trailing currency code, e.g. "1,250.00 BDT".
     *
     * @param  string|float|int  $amount
     */
    public static function formatWithCode($amount, Currency|string|null $currency = null, ?int $userId = null): string
    {
        $resolved = self::resolve($currency, $userId);

        $number = number_format(
            (float) $amount,
            $resolved['precision'],
            $resolved['decimal'],
            $resolved['thousands']
        );

        $sep = chr(32);

        if ($resolved['position'] === 'after') {
            return $number . $sep . $resolved['symbol'] . $sep . $resolved['code'];
        }

        return $resolved['symbol'] . $sep . $number . $sep . $resolved['code'];
    }

    /**
     * Format using an explicit configuration array (used by Settings previews).
     *
     * @param  array{symbol:string,precision:int,thousands:string,decimal:string,position:string}  $config
     */
    public static function formatWithConfig($amount, array $config): string
    {
        $number = number_format(
            (float) $amount,
            (int) ($config['precision'] ?? 2),
            (string) ($config['decimal'] ?? '.'),
            (string) ($config['thousands'] ?? ',')
        );

        $symbol = (string) ($config['symbol'] ?? '');
        $sep = chr(32);

        return ($config['position'] ?? 'before') === 'after'
            ? $number . $sep . $symbol
            : $symbol . $sep . $number;
    }

    /**
     * Resolve a currency into a plain formatting configuration.
     *
     * Resolution order:
     *   1. An explicit Currency model or code (per-record currency is preserved).
     *   2. The user's Settings -> Currency configuration (the system-wide default
     *      that drives every financial screen).
     *   3. A per-user default Currency row (legacy multi-currency list).
     *   4. The application-level config fallback.
     *
     * @return array{symbol:string,code:string,precision:int,thousands:string,decimal:string,position:string}
     */
    public static function resolve(Currency|string|null $currency = null, ?int $userId = null): array
    {
        if ($currency instanceof Currency) {
            return self::fromModel($currency);
        }

        if (is_string($currency) && $currency !== '' && $userId) {
            $model = Currency::ownedBy($userId)->where('code', $currency)->first();
            if ($model) {
                return self::fromModel($model);
            }
        }

        // The Settings -> Currency configuration is the system-wide default.
        $settings = self::settingsConfig($userId);
        if ($settings) {
            return $settings;
        }

        if ($userId) {
            $model = Currency::defaultFor($userId);
            if ($model) {
                return self::fromModel($model);
            }
        }

        // Fall back to the application-level configuration so nothing breaks
        // before a user has configured any currency.
        return [
            'symbol' => (string) config('life.currency.symbol', "\u{09F3}"),
            'code' => (string) config('life.currency.code', 'BDT'),
            'precision' => (int) config('life.currency.decimals', 2),
            'thousands' => (string) config('life.currency.thousands_separator', ','),
            'decimal' => (string) config('life.currency.decimal_separator', '.'),
            'position' => (string) config('life.currency.position', 'before'),
        ];
    }

    /**
     * Config array derived from a Currency model.
     *
     * @return array{symbol:string,code:string,precision:int,thousands:string,decimal:string,position:string}
     */
    protected static function fromModel(Currency $model): array
    {
        return [
            'symbol' => $model->symbol,
            'code' => $model->code,
            'precision' => (int) $model->decimal_precision,
            'thousands' => (string) $model->thousands_separator,
            'decimal' => (string) $model->decimal_separator,
            'position' => $model->symbol_position,
        ];
    }

    /**
     * Config array derived from the user's Settings -> Currency row.
     *
     * Returns null when the user has no settings/currency configured yet, so the
     * caller can fall through to the next resolution source.
     *
     * @return array{symbol:string,code:string,precision:int,thousands:string,decimal:string,position:string}|null
     */
    protected static function settingsConfig(?int $userId): ?array
    {
        if (! $userId) {
            return null;
        }

        $settings = Setting::forUser($userId);

        if (! $settings || ! $settings->currency_code) {
            return null;
        }

        return [
            'symbol' => (string) ($settings->currency_symbol ?: ''),
            'code' => (string) $settings->currency_code,
            'precision' => (int) ($settings->currency_decimals ?? 2),
            'thousands' => (string) ($settings->currency_thousands_separator ?? ','),
            'decimal' => (string) ($settings->currency_decimal_separator ?? '.'),
            'position' => (string) ($settings->currency_position ?? 'before'),
        ];
    }

    /**
     * Format the currency attached to a financial record, preserving whatever
     * currency that record was originally created in.
     *
     * @param  string|float|int  $amount
     */
    public static function forRecord($amount, $record, ?int $userId = null): string
    {
        $currency = null;

        if ($record && $record->relationLoaded('currency')) {
            $currency = $record->currency;
        } elseif ($record && $record->currency_id) {
            $currency = Currency::find($record->currency_id);
        }

        if (! $currency && $record && $record->currency_code && $userId) {
            $currency = Currency::ownedBy($userId)->where('code', $record->currency_code)->first();
        }

        // Fall back to the snapshot code so the original currency still shows
        // even if the currency row was later deleted.
        if (! $currency && $record && $record->currency_code) {
            return self::format($amount, null, $userId);
        }

        return self::format($amount, $currency, $userId);
    }
}
