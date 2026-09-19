<?php

namespace App\Http\Requests;

use App\Models\DailyActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyActivityRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(DailyActivity::STATUSES)],
            // Related goal must belong to the authenticated user.
            'goal_id' => [
                'nullable',
                Rule::exists('goals', 'id')->where('user_id', $userId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'goal_id.exists' => 'The selected goal is invalid.',
            'end_time.date_format' => 'The end time must be a valid time (HH:MM).',
        ];
    }

    /**
     * Derived duration is computed in the controller from start/end times.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();

        if (! empty($data['start_time']) && ! empty($data['end_time'])) {
            try {
                $start = \Carbon\Carbon::parse($data['start_time']);
                $end = \Carbon\Carbon::parse($data['end_time']);
                if ($end->lessThan($start)) {
                    $end->addDay();
                }
                $data['duration_minutes'] = (int) $start->diffInMinutes($end);
            } catch (\Throwable $e) {
                // Keep the manually supplied duration.
            }
        }

        return $data;
    }
}
