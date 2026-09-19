<?php

namespace App\Http\Requests;

use App\Models\Habit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HabitRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'frequency' => ['required', Rule::in(Habit::FREQUENCIES)],
            'start_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(Habit::STATUSES)],
            'activities' => ['nullable', 'array', 'max:50'],
            'activities.*.name' => ['required_with:activities', 'string', 'max:255'],
        ];
    }
}
