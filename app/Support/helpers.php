<?php

use Illuminate\Support\Facades\Config;

if (! function_exists('currency')) {
    /**
     * Format a numeric amount using the application currency settings.
     *
     * Supports the currency symbol, its position (prefix/suffix), decimal
     * precision, thousands separator and decimal separator. Returns a formatted
     * string such as "BDT 30,000.00 TK" or "$30,000.00".
     *
     * @param  float|int|string|null  $amount
     */
    function currency($amount, bool $withSuffix = true): string
    {
        $amount = (float) ($amount ?? 0);

        $symbol = (string) Config::get('currency.symbol', 'BDT');
        $position = Config::get('currency.position', 'prefix');
        $suffix = (string) Config::get('currency.symbol_suffix', '');
        $decimals = (int) Config::get('currency.decimals', 2);
        $decimalSeparator = (string) Config::get('currency.decimal_separator', '.');
        $thousandsSeparator = (string) Config::get('currency.thousands_separator', ',');

        $formatted = number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);

        if ($position === 'suffix') {
            $value = sprintf('%s %s', $formatted, $symbol);
        } else {
            $value = sprintf('%s %s', $symbol, $formatted);
        }

        if ($withSuffix && $suffix !== '') {
            $value = sprintf('%s %s', $value, $suffix);
        }

        return trim($value);
    }
}

if (! function_exists('currency_label')) {
    /**
     * Return a short human label for the configured currency, used in form
     * labels such as "Amount (BDT)". Prefers the currency code, falling back
     * to the symbol.
     */
    function currency_label(): string
    {
        $code = (string) Config::get('currency.code', '');

        if ($code !== '') {
            return $code;
        }

        return (string) Config::get('currency.symbol', '');
    }
}

if (! function_exists('ordinal_suffix')) {
    /**
     * Return the English ordinal suffix for an integer (1st, 2nd, 3rd, 4th ...).
     */
    function ordinal_suffix(int $number): string
    {
        if (! in_array($number % 100, [11, 12, 13], true)) {
            switch ($number % 10) {
                case 1:
                    return 'st';
                case 2:
                    return 'nd';
                case 3:
                    return 'rd';
            }
        }

        return 'th';
    }
}
