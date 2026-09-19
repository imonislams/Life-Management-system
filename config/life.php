<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Central currency configuration used by the `money()` helper. The defaults
    | match the existing Money Management formatting ("৳ 1,234.00 TK") so all
    | previously built financial screens keep rendering identically.
    |
    */

    'currency' => [
        'symbol' => env('CURRENCY_SYMBOL', "\u{09F3}"),
        'decimals' => (int) env('CURRENCY_DECIMALS', 2),
        'thousands_separator' => env('CURRENCY_THOUSANDS_SEPARATOR', ','),
        'decimal_separator' => env('CURRENCY_DECIMAL_SEPARATOR', '.'),
        'position' => env('CURRENCY_POSITION', 'before'),
        'suffix' => env('CURRENCY_SUFFIX', 'TK'),
    ],

];
