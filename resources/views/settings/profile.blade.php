@php
use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="title">Profile Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Profile Settings</h2>
        <p class="card-subtitle">Manage your personal account information.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Current avatar -->
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                @if($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path))
                <img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="Profile photo" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                @else
                <span class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                @endif
                <div>
                    <div style="font-weight: 600;">{{ $user->name }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        Member since {{ user_date($user->created_at) }}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="name" class="form-label">Full Name <span style="color: var(--danger-color);">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                @error('name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address <span style="color: var(--danger-color);">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                @error('email')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="profile_photo" class="form-label">Profile Photo (Optional)</label>
                <input id="profile_photo" type="file" name="profile_photo" class="form-control" accept="image/*">
                @error('profile_photo')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            @if($user->profile_photo_path)
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="remove_photo" value="1" style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Remove current profile photo</span>
                </label>
            </div>
            @endif

            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 0.875rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.25rem;">Account Information</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    Account created: <strong style="color: var(--text-main);">{{ $user->created_at->format('M d, Y H:i') }}</strong>
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    Last updated: <strong style="color: var(--text-main);">{{ $user->updated_at->format('M d, Y H:i') }}</strong>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center;">
                <button type="submit" class="btn-primary">Save Profile</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>