<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Central currency configuration used by the currency() helper across the
    | whole application. Every money-related module must format amounts through
    | that helper so the symbol, position, precision and separators stay
    | consistent and can be changed from one single place.
    |
    | "position" accepts "prefix" (symbol before the amount) or "suffix"
    | (symbol after the amount).
    |
    | "symbol_suffix" is an optional secondary label shown after the amount,
    | useful for currencies commonly written like "৳ 30,000.00 TK".
    |
    */

    'symbol' => env('CURRENCY_SYMBOL', '৳'),

    'position' => env('CURRENCY_POSITION', 'prefix'),

    'code' => env('CURRENCY_CODE', 'BDT'),

    'symbol_suffix' => env('CURRENCY_SYMBOL_SUFFIX', 'TK'),

    'decimals' => (int) env('CURRENCY_DECIMALS', 2),

    'decimal_separator' => env('CURRENCY_DECIMAL_SEPARATOR', '.'),

    'thousands_separator' => env('CURRENCY_THOUSANDS_SEPARATOR', ','),

];
