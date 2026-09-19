<?php

namespace App\Http\Requests;

use App\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'target_value' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', Rule::in(Goal::PRIORITIES)],
            'status' => ['required', Rule::in(Goal::STATUSES)],
            'progress_type' => ['required', Rule::in(Goal::PROGRESS_TYPES)],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
        ];

        // Measurable goals require a numeric target; qualitative goals do not.
        if ($this->input('progress_type') === 'measurable') {
            $rules['target_amount'] = ['required', 'numeric', 'gt:0'];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'progress.min' => 'Progress must be between 0 and 100.',
            'progress.max' => 'Progress must be between 0 and 100.',
            'target_amount.required' => 'A measurable goal needs a target amount.',
        ];
    }

    /**
     * Normalise the payload: qualitative goals get a numeric progress value,
     * measurable goals never take a hand-typed percentage.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();

        if (($data['progress_type'] ?? 'qualitative') === 'measurable') {
            unset($data['progress']);
        } else {
            $data['progress'] = $data['progress'] ?? 0;
            $data['target_amount'] = null;
            $data['current_amount'] = null;
        }

        return $data;
    }
}
