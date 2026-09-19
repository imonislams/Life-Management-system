<?php

namespace App\Http\Middleware;

use App\Support\UserPreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the authenticated user's saved timezone for the duration of the
 * request, so dates and times render consistently with their General settings.
 */
class ApplyUserPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $timezone = UserPreference::timezone($request->user()->id);

            // PHP and the framework honour this for the rest of the request.
            // Carbon reads this default too, so "now" becomes user-local
            // without disturbing the clock.
            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);
        }

        return $next($request);
    }
}
