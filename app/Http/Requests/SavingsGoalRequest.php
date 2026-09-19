<?php

namespace App\Http\Requests;

use App\Models\SavingsGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavingsGoalRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(SavingsGoal::STATUSES)],
            // Currency must belong to the authenticated user and be active.
            'currency_id' => [
                'nullable',
                Rule::exists('currencies', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('is_active', true);
                }),
            ],
        ];
    }
}
