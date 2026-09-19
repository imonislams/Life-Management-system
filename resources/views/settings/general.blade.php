@php
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="title">General Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">General Settings</h2>
        <p class="card-subtitle">Basic application preferences for your workspace.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="app_name" class="form-label">Application / Company Name</label>
                <input id="app_name" type="text" name="app_name" value="{{ old('app_name', $settings->app_name) }}" class="form-control" placeholder="{{ config('app.name') }}">
                @error('app_name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="logo" class="form-label">Logo (Optional)</label>
                    @if($settings->logo_path)
                    <div style="display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.5rem;">
                        @if(Storage::disk('public')->exists($settings->logo_path))
                        <img src="{{ Storage::disk('public')->url($settings->logo_path) }}" alt="Current logo" style="height: 32px; width: auto; max-width: 96px; object-fit: contain; border: 1px solid var(--border-color); border-radius: 0.25rem; padding: 2px; background: #fff;">
                        @endif
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Current logo uploaded.</span>
                    </div>
                    @endif
                    <input id="logo" type="file" name="logo" class="form-control" accept="image/*">
                    @error('logo')<div class="error-msg">{{ $message }}</div>@enderror
                    @if($settings->logo_path)
                    <div class="form-group" style="margin-top: 0.5rem; margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="remove_logo" value="1" style="width: 16px; height: 16px;">
                            <span class="form-label" style="margin: 0;">Remove current logo</span>
                        </label>
                    </div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="favicon" class="form-label">Favicon (Optional)</label>
                    @if($settings->favicon_path)
                    <div style="display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.5rem;">
                        @if(Storage::disk('public')->exists($settings->favicon_path))
                        <img src="{{ Storage::disk('public')->url($settings->favicon_path) }}" alt="Current favicon" style="height: 32px; width: 32px; object-fit: contain; border: 1px solid var(--border-color); border-radius: 0.25rem; padding: 2px; background: #fff;">
                        @endif
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Current favicon uploaded.</span>
                    </div>
                    @endif
                    <input id="favicon" type="file" name="favicon" class="form-control" accept="image/*">
                    @error('favicon')<div class="error-msg">{{ $message }}</div>@enderror
                    @if($settings->favicon_path)
                    <div class="form-group" style="margin-top: 0.5rem; margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="remove_favicon" value="1" style="width: 16px; height: 16px;">
                            <span class="form-label" style="margin: 0;">Remove current favicon</span>
                        </label>
                    </div>
                    @endif
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="language" class="form-label">Default Language <span style="color: var(--danger-color);">*</span></label>
                    <select id="language" name="language" class="form-control" required>
                        @foreach(Setting::LANGUAGES as $code => $label)
                        <option value="{{ $code }}" {{ old('language', $settings->language) === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('language')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="timezone" class="form-label">Timezone <span style="color: var(--danger-color);">*</span></label>
                    <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $settings->timezone) }}" list="timezoneList" class="form-control" required>
                    <datalist id="timezoneList">
                        <option value="UTC"></option>
                        <option value="Asia/Dhaka"></option>
                        <option value="Asia/Kolkata"></option>
                        <option value="Asia/Dubai"></option>
                        <option value="Asia/Karachi"></option>
                        <option value="Asia/Singapore"></option>
                        <option value="Asia/Tokyo"></option>
                        <option value="Europe/London"></option>
                        <option value="Europe/Paris"></option>
                        <option value="America/New_York"></option>
                        <option value="America/Chicago"></option>
                        <option value="America/Los_Angeles"></option>
                        <option value="Australia/Sydney"></option>
                    </datalist>
                    @error('timezone')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="date_format" class="form-label">Date Format <span style="color: var(--danger-color);">*</span></label>
                    <select id="date_format" name="date_format" class="form-control" required>
                        @foreach(Setting::DATE_FORMATS as $format => $example)
                        <option value="{{ $format }}" {{ old('date_format', $settings->date_format) === $format ? 'selected' : '' }}>{{ $example }}</option>
                        @endforeach
                    </select>
                    @error('date_format')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="time_format" class="form-label">Time Format <span style="color: var(--danger-color);">*</span></label>
                    <select id="time_format" name="time_format" class="form-control" required>
                        @foreach(Setting::TIME_FORMATS as $value => $label)
                        <option value="{{ $value }}" {{ old('time_format', $settings->time_format) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('time_format')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="week_start" class="form-label">Week Start Day <span style="color: var(--danger-color);">*</span></label>
                    <select id="week_start" name="week_start" class="form-control" required>
                        @foreach(Setting::WEEK_DAYS as $value => $label)
                        <option value="{{ $value }}" {{ (int) old('week_start', $settings->week_start) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('week_start')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save General Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>