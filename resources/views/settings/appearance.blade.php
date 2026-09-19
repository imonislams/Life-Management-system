@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Appearance Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Appearance Settings</h2>
        <p class="card-subtitle">Personalise how the interface looks for your account. The existing design system stays intact.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.appearance.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Theme <span style="color: var(--danger-color);">*</span></label>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach(Setting::THEMES as $value => $label)
                    <label style="display: flex; align-items: center; gap: 0.4rem; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 0.5rem 0.75rem; cursor: pointer; {{ old('theme', $settings->theme) === $value ? 'background-color: #eff6ff; border-color: #bfdbfe;' : '' }}">
                        <input type="radio" name="theme" value="{{ $value }}" {{ old('theme', $settings->theme) === $value ? 'checked' : '' }}>
                        <span style="font-size: 0.875rem;">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('theme')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="dashboard_layout" class="form-label">Dashboard Layout Preference <span style="color: var(--danger-color);">*</span></label>
                <select id="dashboard_layout" name="dashboard_layout" class="form-control" required>
                    @foreach(Setting::DASHBOARD_LAYOUTS as $value => $label)
                    <option value="{{ $value }}" {{ old('dashboard_layout', $settings->dashboard_layout) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('dashboard_layout')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="primary_color" class="form-label">Primary Color <span style="color: var(--danger-color);">*</span></label>
                <select id="primary_color" name="primary_color" class="form-control" required>
                    @foreach(Setting::PRIMARY_COLORS as $value => $label)
                    <option value="{{ $value }}" {{ old('primary_color', $settings->primary_color) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="display: flex; gap: 0.35rem; margin-top: 0.5rem;">
                    @foreach(Setting::PRIMARY_COLORS as $value => $label)
                    <span title="{{ $label }}" style="width: 20px; height: 20px; border-radius: 0.25rem; background: {{ $value }}; border: 1px solid var(--border-color);"></span>
                    @endforeach
                </div>
                @error('primary_color')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="sidebar_collapsed" value="1" {{ old('sidebar_collapsed', $settings->sidebar_collapsed) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Sidebar collapsed by default</span>
                </label>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="compact_mode" value="1" {{ old('compact_mode', $settings->compact_mode) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Compact mode (tighter spacing where compatible)</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Appearance Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>