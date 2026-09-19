<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'currency_name' => ['required', 'string', 'max:64'],
            'currency_code' => ['required', 'string', 'max:8'],
            'currency_symbol' => ['required', 'string', 'max:16'],
            'currency_decimals' => ['required', 'integer', 'between:0,4'],
            'currency_thousands_separator' => ['required', 'string', 'max:4'],
            'currency_decimal_separator' => ['required', 'string', 'max:4'],
            'currency_position' => ['required', Rule::in(array_keys(Setting::CURRENCY_POSITIONS))],
            'currency_suffix' => ['nullable', 'string', 'max:8'],
            'currency_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'currency_name' => 'currency name',
            'currency_code' => 'currency code',
            'currency_symbol' => 'currency symbol',
            'currency_decimals' => 'decimal precision',
        ];
    }
}
