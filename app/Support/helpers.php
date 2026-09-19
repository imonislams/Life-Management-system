<?php

if (! function_exists('user_pref')) {
    /**
     * Resolve the authenticated user's saved General preference.
     */
    function user_pref(string $key, $default = null)
    {
        return \App\Support\UserPreference::get($key, $default);
    }
}

if (! function_exists('user_date')) {
    /**
     * Format a date using the user's date format and timezone.
     */
    function user_date($date): string
    {
        return \App\Support\UserPreference::formatDate($date);
    }
}

if (! function_exists('user_time')) {
    /**
     * Format a time using the user's 12h/24h preference.
     */
    function user_time($time): string
    {
        return \App\Support\UserPreference::formatTime($time);
    }
}

if (! function_exists('user_datetime')) {
    /**
     * Format a datetime using the user's date and time preferences.
     */
    function user_datetime($value): string
    {
        return \App\Support\UserPreference::formatDateTime($value);
    }
}

if (! function_exists('money')) {
    /**
     * Format a monetary amount using the existing application currency settings
     * (symbol, decimal precision, separators, position, suffix).
     *
     * The Money Management module renders amounts as the local currency, so the
     * defaults used here reproduce that exact output and nothing in the existing
     * financial UI changes.
     */
    function money($amount, ?bool $withSuffix = null): string
    {
        $amount = (float) $amount;

        $symbol = (string) config('life.currency.symbol', "\u{09F3}");
        $decimals = (int) config('life.currency.decimals', 2);
        $thousands = (string) config('life.currency.thousands_separator', ',');
        $decimalPoint = (string) config('life.currency.decimal_separator', '.');
        $position = (string) config('life.currency.position', 'before');
        $suffix = $withSuffix === false ? '' : (string) config('life.currency.suffix', 'TK');

        $number = number_format($amount, $decimals, $decimalPoint, $thousands);

        $formatted = $position === 'after'
            ? sprintf('%s %s', $number, $symbol)
            : sprintf('%s %s', $symbol, $number);

        return $suffix !== '' ? sprintf('%s %s', $formatted, $suffix) : $formatted;
    }
}
