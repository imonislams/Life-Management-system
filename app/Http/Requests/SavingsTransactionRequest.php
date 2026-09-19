<?php

namespace App\Http\Requests;

use App\Models\SavingsTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavingsTransactionRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            // The goal must belong to the authenticated user.
            'savings_goal_id' => [
                'required',
                Rule::exists('savings_goals', 'id')->where('user_id', $userId),
            ],
            'type' => ['required', Rule::in(SavingsTransaction::TYPES)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            // Currency must belong to the user and be active (optional).
            'currency_id' => [
                'nullable',
                Rule::exists('currencies', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('is_active', true);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'savings_goal_id.exists' => 'The selected savings goal is invalid.',
            'currency_id.exists' => 'The selected currency is invalid.',
        ];
    }
}
