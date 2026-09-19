<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Auth;

/**
 * Enforces the "minimum necessary data" privacy rule.
 *
 * Two responsibilities:
 *   1. Decide what may be embedded into the semantic index.
 *   2. Redact and fence untrusted user content before it is sent to a provider.
 *
 * Nothing here ever includes passwords, tokens, API keys or database
 * credentials: those values are never passed in, and the redactor removes
 * anything that looks like a secret as a defence in depth.
 */
class AIPrivacyService
{
    /**
     * Whether semantic indexing is permitted for a user.
     *
     * Both the global switch and the per-user preference must allow it.
     */
    public function canIndex(?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        if (! $userId) {
            return false;
        }

        if (! config('ai.enabled')) {
            return false;
        }

        $settings = \App\Models\AiSetting::forUser($userId);

        return (bool) $settings->semantic_indexing_enabled;
    }

    /**
     * Redact obvious secrets from a piece of text.
     *
     * This is defensive: the pipeline only ever passes personal content, but if
     * a user typed an API key into a note we still must not forward it.
     */
    public function redact(string $text): string
    {
        // Common API-key shapes (sk-..., long hex/token blobs).
        $text = preg_replace('/\b(sk|pk|rk)-[A-Za-z0-9_\-]{12,}\b/', '[redacted-key]', $text) ?? $text;
        $text = preg_replace('/\b[A-Za-z0-9_\-]{40,}\b/', '[redacted-token]', $text) ?? $text;

        // Passwords / secrets written as "password: xyz".
        $text = preg_replace('/\b(password|pass|secret|token|api[_\- ]?key)\s*[:=]\s*\S+/i', '$1: [redacted]', $text) ?? $text;

        return $text;
    }

    /**
     * Optionally redact e-mail addresses from content sent to the provider.
     */
    public function redactEmail(string $text): string
    {
        if (! config('ai.privacy.redact_email')) {
            return $text;
        }

        return preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[redacted-email]', $text) ?? $text;
    }

    /**
     * Full sanitisation applied to every untrusted user-supplied string before
     * it reaches the model.
     */
    public function sanitizeUntrusted(string $text, int $maxChars = 2000): string
    {
        $text = $this->redact($text);
        $text = $this->redactEmail($text);

        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars) . '…';
        }

        return $text;
    }

    /**
     * Wrap untrusted content so the model treats it strictly as data, never as
     * instructions (prompt-injection defence).
     */
    public function fence(string $text): string
    {
        // Neutralise any attempt to close the fence early.
        $text = str_replace(['<<DATA', 'DATA>>>'], ['‹DATA', 'DATA›'], $text);

        return "<<<DATA\n{$text}\nDATA>>>";
    }
}
