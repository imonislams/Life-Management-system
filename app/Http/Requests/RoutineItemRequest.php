<?php

namespace App\Http\Requests;

use App\Models\RoutineItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoutineItemRequest extends FormRequest
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
            'recurrence_type' => ['required', Rule::in(RoutineItem::RECURRENCE_TYPES)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(RoutineItem::STATUSES)],
            'interval_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'min:0', 'max:6'],
        ];

        // A custom-days routine must select at least one weekday, and an
        // interval routine must specify how many days apart it repeats.
        if ($this->input('recurrence_type') === 'custom_days') {
            $rules['days_of_week'] = ['required', 'array', 'min:1'];
        }

        if ($this->input('recurrence_type') === 'interval') {
            $rules['interval_days'] = ['required', 'integer', 'min:1', 'max:365'];
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
            'start_time.date_format' => 'The start time must be a valid time (HH:MM).',
            'end_time.date_format' => 'The end time must be a valid time (HH:MM).',
            'days_of_week.required' => 'Select at least one day for a custom days routine.',
            'interval_days.required' => 'Enter how many days apart the routine should repeat.',
        ];
    }

    /**
     * Prepare the data for validation: join the selected weekdays into the
     * stored comma-separated string format.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('days_of_week') && is_array($this->input('days_of_week'))) {
            $days = array_map('intval', $this->input('days_of_week'));
            $days = array_values(array_unique(array_filter($days, fn ($d) => $d >= 0 && $d <= 6)));
            sort($days);
            $this->merge(['days_of_week_list' => implode(',', $days)]);
        }
    }

    /**
     * Return the validated data ready to persist (weekdays stored as a string).
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();
        unset($data['days_of_week']);

        $data['days_of_week'] = $this->input('recurrence_type') === 'custom_days'
            ? $this->input('days_of_week_list')
            : null;

        if ($this->input('recurrence_type') !== 'interval') {
            $data['interval_days'] = null;
        }

        return $data;
    }
}
