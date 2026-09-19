<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppearanceSettingsRequest extends FormRequest
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
            'theme' => ['required', Rule::in(array_keys(Setting::THEMES))],
            'sidebar_collapsed' => ['nullable', 'boolean'],
            'compact_mode' => ['nullable', 'boolean'],
            'dashboard_layout' => ['required', Rule::in(array_keys(Setting::DASHBOARD_LAYOUTS))],
            'primary_color' => ['required', 'string', Rule::in(array_keys(Setting::PRIMARY_COLORS))],
        ];
    }

    public function attributes(): array
    {
        return [
            'dashboard_layout' => 'dashboard layout',
            'primary_color' => 'primary color',
        ];
    }
}
