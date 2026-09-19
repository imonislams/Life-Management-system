<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalise the currency code before validation so "usd" and "usd " both
     * become "USD" and uniqueness checks behave predictably.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currency = $this->route('currency');
        $ignoreId = $currency instanceof Currency ? $currency->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('currencies', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($ignoreId),
            ],
            'code' => [
                'required',
                'string',
                'max:8',
                'regex:/^[A-Z]{2,8}$/',
                Rule::unique('currencies', 'code')
                    ->where('user_id', $this->user()->id)
                    ->ignore($ignoreId),
            ],
            'symbol' => ['required', 'string', 'max:16'],
            'country' => ['nullable', 'string', 'max:64'],
            'decimal_precision' => ['required', 'integer', 'between:0,4'],
            'thousands_separator' => ['required', 'string', 'max:4'],
            'decimal_separator' => ['required', 'string', 'max:4'],
            'symbol_position' => ['required', Rule::in(array_keys(Currency::SYMBOL_POSITIONS))],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The currency code must contain 2-8 uppercase letters (e.g. USD).',
            'code.unique' => 'You already have a currency with this code.',
            'name.unique' => 'You already have a currency with this name.',
        ];
    }

    public function attributes(): array
    {
        return [
            'decimal_precision' => 'decimal precision',
            'thousands_separator' => 'thousands separator',
            'decimal_separator' => 'decimal separator',
            'symbol_position' => 'symbol position',
        ];
    }
}
