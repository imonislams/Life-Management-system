<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves and applies the authenticated user's General settings:
 * application name, timezone, date format, time format and week start day.
 *
 * These are read from the user's saved settings row so every page renders
 * dates and times consistently with their configuration.
 */
class UserPreference
{
    /**
     * Cached settings per user id for the current request.
     *
     * @var array<int, Setting|null>
     */
    protected static array $cache = [];

    /**
     * The settings model for a user (cached).
     */
    public static function settings(?int $userId = null): ?Setting
    {
        $userId = $userId ?? Auth::id();

        if (! $userId) {
            return null;
        }

        if (! array_key_exists($userId, self::$cache)) {
            self::$cache[$userId] = Setting::where('user_id', $userId)->first();
        }

        return self::$cache[$userId];
    }

    /**
     * Resolve a preference value with an application-config fallback.
     */
    public static function get(string $key, $default = null, ?int $userId = null)
    {
        $settings = self::settings($userId);

        if ($settings && $settings->{$key} !== null && $settings->{$key} !== '') {
            return $settings->{$key};
        }

        return $default;
    }

    /**
     * The user's configured timezone (falls back to the app timezone).
     */
    public static function timezone(?int $userId = null): string
    {
        $timezone = self::get('timezone', null, $userId);

        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        return (string) config('app.timezone', 'UTC');
    }

    /**
     * Carbon date format string (e.g. "M d, Y").
     */
    public static function dateFormat(?int $userId = null): string
    {
        return (string) self::get('date_format', 'M d, Y', $userId);
    }

    /**
     * Time format: "12" or "24".
     */
    public static function timeFormat(?int $userId = null): string
    {
        return (string) self::get('time_format', '12', $userId);
    }

    /**
     * Week start day as a Carbon day constant (0 = Sunday).
     */
    public static function weekStart(?int $userId = null): int
    {
        return (int) self::get('week_start', 0, $userId);
    }

    /**
     * The user's application/company name (falls back to config).
     */
    public static function appName(?int $userId = null): string
    {
        $name = self::get('app_name', null, $userId);

        return $name ?: (string) config('app.name', 'Life Management System');
    }

    /**
     * Format a date using the user's date format and timezone.
     */
    public static function formatDate($date, ?int $userId = null): string
    {
        if (! $date) {
            return '—';
        }

        // A plain date (no time component) is rendered as-is so it never
        // shifts by a day when a timezone is applied.
        return self::toUserTime($date, $userId)->format(self::dateFormat($userId));
    }

    /**
     * Format a time using the user's time format (12h or 24h).
     */
    public static function formatTime($time, ?int $userId = null): string
    {
        if (! $time) {
            return '—';
        }

        $format = self::timeFormat($userId) === '24' ? 'H:i' : 'h:i A';

        try {
            return Carbon::parse($time)->format($format);
        } catch (\Throwable $e) {
            return substr((string) $time, 0, 5);
        }
    }

    /**
     * Format a datetime using both the user's date and time formats.
     */
    public static function formatDateTime($value, ?int $userId = null): string
    {
        if (! $value) {
            return '—';
        }

        $format = self::dateFormat($userId)
            . (self::timeFormat($userId) === '24' ? ' H:i' : ' h:i A');

        return self::toUserTime($value, $userId)->format($format);
    }

    /**
     * Convert a value into the user's timezone.
     */
    public static function toUserTime($value, ?int $userId = null): Carbon
    {
        $carbon = $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse($value);

        return $carbon->setTimezone(self::timezone($userId));
    }

    /**
     * "Now" in the user's timezone.
     */
    public static function now(?int $userId = null): Carbon
    {
        return Carbon::now(self::timezone($userId));
    }

    /**
     * "Today" in the user's timezone.
     */
    public static function today(?int $userId = null): Carbon
    {
        return Carbon::today(self::timezone($userId));
    }

    /**
     * Clear the request cache (useful after saving settings).
     */
    public static function flush(): void
    {
        self::$cache = [];
        Setting::query()->get()->each(function ($setting) {
            self::$cache[$setting->user_id] = $setting;
        });
    }
}
