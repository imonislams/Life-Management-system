@php
use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="title">Security Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Security Settings</h2>
        <p class="card-subtitle">Keep your account secure by updating your password regularly.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <h3 class="card-title" style="font-size: 1.05rem; margin-bottom: 1rem;">Change Password</h3>

        <form method="POST" action="{{ route('settings.password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password" class="form-label">Current Password <span style="color: var(--danger-color);">*</span></label>
                <input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password">
                @error('current_password')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="password" class="form-label">New Password <span style="color: var(--danger-color);">*</span></label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
                    @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password <span style="color: var(--danger-color);">*</span></label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1rem;">
                <button type="submit" class="btn-primary">Update Password</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>

    <div class="card" style="max-width: 720px;">
        <h3 class="card-title" style="font-size: 1.05rem; margin-bottom: 1rem;">Account Security Overview</h3>

        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Account</span>
                <span style="font-weight: 600;">{{ $user->name }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Email</span>
                <span style="font-weight: 600;">{{ $user->email }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Password</span>
                <span style="font-weight: 600;">Hashed and securely stored</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Last updated</span>
                <span style="font-weight: 600;">{{ $user->updated_at->diffForHumans() }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Data visibility</span>
                <span style="font-weight: 600;">Private — only visible to you</span>
            </div>
        </div>

        <div class="alert-success" style="margin-top: 1rem;">
            All of your income, salary, expenses, savings, recurring finance, tasks, habits, routine, goals, progress and events are visible only to your account.
        </div>
    </div>
</x-app-layout>