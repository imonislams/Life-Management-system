<?php

namespace App\Http\Requests;

use App\Models\RoutineOccurrence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoutineOccurrenceRequest extends FormRequest
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
            'occurrence_date' => ['required', 'date'],
            'status' => ['required', Rule::in(RoutineOccurrence::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
