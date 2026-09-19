<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppearanceSettingsRequest;
use App\Http\Requests\CurrencySettingsRequest;
use App\Http\Requests\FinanceSettingsRequest;
use App\Http\Requests\GeneralSettingsRequest;
use App\Http\Requests\NotificationSettingsRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Currency;
use App\Models\Setting;
use App\Support\UserPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Per-user settings.
 *
 * Every method resolves the settings row from the authenticated user only;
 * a user id is never read from the request, so one user can never read or
 * modify another user's settings.
 */
class SettingsController extends Controller
{
    /**
     * Show the settings overview page.
     */
    public function index()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.index', compact('settings'));
    }

    // ---------------------------------------------------------------------
    // General
    // ---------------------------------------------------------------------
    public function general()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.general', compact('settings'));
    }

    public function updateGeneral(GeneralSettingsRequest $request)
    {
        $settings = Setting::forUser($request->user()->id);
        $data = $request->safe()->only([
            'app_name',
            'language',
            'timezone',
            'date_format',
            'time_format',
            'week_start',
        ]);

        // Explicit "Remove current logo / favicon" requests delete the file from
        // disk and clear the path so the layout falls back to the brand name.
        if ($request->boolean('remove_logo') && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('remove_favicon') && $settings->favicon_path) {
            Storage::disk('public')->delete($settings->favicon_path);
            $data['favicon_path'] = null;
        }

        // A fresh upload always wins over a removal request and replaces the old
        // file, so the two are never left half-applied.
        if ($request->hasFile('logo')) {
            if ($settings->logo_path && $settings->logo_path !== ($data['logo_path'] ?? null)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path && $settings->favicon_path !== ($data['favicon_path'] ?? null)) {
                Storage::disk('public')->delete($settings->favicon_path);
            }
            $data['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        $settings->update($data);

        return redirect()->route('settings.general')->with('status', 'General settings saved successfully.');
    }

    // ---------------------------------------------------------------------
    // Currency
    // ---------------------------------------------------------------------
    public function currency()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.currency', compact('settings'));
    }

    public function updateCurrency(CurrencySettingsRequest $request)
    {
        $userId = $request->user()->id;
        $settings = Setting::forUser($userId);

        $data = $request->safe()->only([
            'currency_name',
            'currency_code',
            'currency_symbol',
            'currency_decimals',
            'currency_thousands_separator',
            'currency_decimal_separator',
            'currency_position',
            'currency_suffix',
        ]);

        // Unchecked checkbox means "inactive".
        $data['currency_active'] = $request->boolean('currency_active');

        // currency_suffix is NOT NULL in the database, so an empty submission is
        // normalised to an empty string rather than null.
        if (! array_key_exists('currency_suffix', $data) || $data['currency_suffix'] === null) {
            $data['currency_suffix'] = '';
        }

        $settings->update($data);

        // Keep the per-user currency registry in sync: the Settings currency is
        // the system-wide default, so if the user owns a matching currency row it
        // is promoted to default (existing financial records are never touched).
        $existing = Currency::ownedBy($userId)
            ->where('code', $data['currency_code'])
            ->first();

        if ($existing) {
            $existing->makeDefault();
        } else {
            Currency::ownedBy($userId)->update(['is_default' => false]);
        }

        // Refresh the cached preferences so the new currency applies immediately.
        UserPreference::flush();

        return redirect()->route('settings.currency')
            ->with('status', 'System currency saved. It now applies across your whole workspace.');
    }

    // ---------------------------------------------------------------------
    // Salary / Savings / Income / Expense / Recurring (one finance form)
    // ---------------------------------------------------------------------
    public function salary()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.salary', compact('settings'));
    }

    public function savings()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.savings', compact('settings'));
    }

    public function income()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.income', compact('settings'));
    }

    public function expense()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.expense', compact('settings'));
    }

    public function recurring()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.recurring', compact('settings'));
    }

    public function updateFinance(FinanceSettingsRequest $request)
    {
        $settings = Setting::forUser($request->user()->id);

        $data = $request->safe()->only([
            'salary_currency_code',
            'salary_frequency',
            'salary_payment_day',
            'salary_reminder_date',
            'salary_display_format',
            'savings_currency_code',
            'savings_monthly_target',
            'savings_default_goal',
            'savings_reminder_date',
            'savings_progress_display',
            'savings_transaction_display',
            'income_currency_code',
            'income_display_format',
            'income_summary_preference',
            'expense_currency_code',
            'expense_display_format',
            'expense_monthly_limit',
            'recurring_display_preference',
            'recurring_frequency',
            'recurring_summary_preference',
        ]);

        $data['salary_reminder_enabled'] = $request->boolean('salary_reminder_enabled');
        $data['savings_reminder_enabled'] = $request->boolean('savings_reminder_enabled');
        $data['income_monthly_overview'] = $request->boolean('income_monthly_overview');
        $data['expense_warning_enabled'] = $request->boolean('expense_warning_enabled');

        $settings->update($data);

        return redirect()->back()->with('status', 'Finance settings saved successfully.');
    }

    // ---------------------------------------------------------------------
    // Notifications (saved preferences only)
    // ---------------------------------------------------------------------
    public function notifications()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.notifications', compact('settings'));
    }

    public function updateNotifications(NotificationSettingsRequest $request)
    {
        $settings = Setting::forUser($request->user()->id);

        $data = $request->safe()->only([
            'notify_reminder_time',
            'notify_quiet_start',
            'notify_quiet_end',
        ]);

        foreach (
            [
                'notifications_enabled',
                'notify_task_reminder',
                'notify_habit_reminder',
                'notify_goal_deadline',
                'notify_event_reminder',
                'notify_salary_reminder',
                'notify_savings_reminder',
                'notify_recurring_reminder',
                'notify_email',
                'notify_browser',
            ] as $flag
        ) {
            $data[$flag] = $request->boolean($flag);
        }

        $settings->update($data);

        return redirect()->route('settings.notifications')->with('status', 'Notification preferences saved successfully.');
    }

    // ---------------------------------------------------------------------
    // Appearance
    // ---------------------------------------------------------------------
    public function appearance()
    {
        $settings = Setting::forUser(Auth::id());

        return view('settings.appearance', compact('settings'));
    }

    public function updateAppearance(AppearanceSettingsRequest $request)
    {
        $settings = Setting::forUser($request->user()->id);

        $data = $request->safe()->only(['theme', 'dashboard_layout', 'primary_color']);
        $data['sidebar_collapsed'] = $request->boolean('sidebar_collapsed');
        $data['compact_mode'] = $request->boolean('compact_mode');

        $settings->update($data);

        return redirect()->route('settings.appearance')->with('status', 'Appearance settings saved successfully.');
    }

    // ---------------------------------------------------------------------
    // Profile
    // ---------------------------------------------------------------------
    public function profile()
    {
        $user = Auth::user();
        $settings = Setting::forUser($user->id);

        return view('settings.profile', compact('user', 'settings'));
    }

    /**
     * AI status page.
     *
     * Reports the health of the 100% LOCAL AI stack (Ollama + vector DB + the
     * selected models). No secrets are ever displayed: only model names, URLs
     * from config, and record counts.
     */
    public function ai(\App\Services\AI\AIHealthService $health)
    {
        $settings = Setting::forUser(Auth::id());
        $report = $health->report(Auth::id());
        $hint = $health->hint($report);

        return view('settings.ai', compact('settings', 'report', 'hint'));
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $data = $request->safe()->only(['name', 'email']);

        if ($request->boolean('remove_photo') && $user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = $request->file('profile_photo')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('settings.profile')->with('status', 'Profile updated successfully.');
    }

    // ---------------------------------------------------------------------
    // Security
    // ---------------------------------------------------------------------
    public function security()
    {
        $user = Auth::user();
        $settings = Setting::forUser($user->id);

        return view('settings.security', compact('user', 'settings'));
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        return redirect()->route('settings.security')->with('status', 'Password updated successfully.');
    }
}
