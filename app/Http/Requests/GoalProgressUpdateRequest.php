<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalProgressUpdateRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            // For measurable goals this is the amount to add to current progress.
            'progress_value' => ['nullable', 'numeric'],
            'time_spent_minutes' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
