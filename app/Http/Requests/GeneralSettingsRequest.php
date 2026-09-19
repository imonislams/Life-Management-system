<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'app_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,jpg,jpeg,svg', 'max:512'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'language' => ['required', 'string', Rule::in(array_keys(Setting::LANGUAGES))],
            'timezone' => ['required', 'string', 'timezone'],
            'date_format' => ['required', 'string', Rule::in(array_keys(Setting::DATE_FORMATS))],
            'time_format' => ['required', 'string', Rule::in(array_keys(Setting::TIME_FORMATS))],
            'week_start' => ['required', 'integer', 'between:0,6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'app_name' => 'application name',
            'week_start' => 'week start day',
            'remove_logo' => 'logo removal',
            'remove_favicon' => 'favicon removal',
        ];
    }
}
