<?php

namespace App\Services\AI\Exceptions;

use RuntimeException;

/**
 * A safe, user-presentable AI layer failure.
 *
 * The message is deliberately generic so provider internals (URLs, keys, stack
 * traces) can never leak into the UI. Technical detail is logged separately.
 */
class AIServiceException extends RuntimeException
{
    public const CODE_NOT_CONFIGURED = 'not_configured';

    public const CODE_DISABLED = 'disabled';

    public const CODE_UNAUTHORIZED = 'unauthorized';

    public const CODE_RATE_LIMITED = 'rate_limited';

    public const CODE_TIMEOUT = 'timeout';

    public const CODE_PROVIDER_ERROR = 'provider_error';

    public const CODE_MALFORMED_RESPONSE = 'malformed_response';

    public const CODE_VECTOR_UNAVAILABLE = 'vector_unavailable';

    public const CODE_NETWORK = 'network';

    public const CODE_MODEL_MISSING = 'model_missing';

    public const CODE_INVALID_INPUT = 'invalid_input';

    private string $reason;

    public function __construct(string $reason, string $message = '', ?\Throwable $previous = null)
    {
        $this->reason = $reason;

        parent::__construct($message !== '' ? $message : self::defaultMessage($reason), 0, $previous);
    }

    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * A friendly, secret-free message for the end user.
     */
    public static function defaultMessage(string $reason): string
    {
        return match ($reason) {
            self::CODE_NOT_CONFIGURED => 'The AI assistant is not configured yet. Please contact your administrator.',
            self::CODE_DISABLED => 'The AI assistant is currently disabled in your settings.',
            self::CODE_UNAUTHORIZED => 'The AI provider rejected the configured credentials.',
            self::CODE_RATE_LIMITED => 'You have reached the AI usage limit. Please wait a moment and try again.',
            self::CODE_TIMEOUT => 'The local AI model took too long to respond. Please try again.',
            self::CODE_MALFORMED_RESPONSE => 'The local AI model returned an unexpected response. Please try again.',
            self::CODE_VECTOR_UNAVAILABLE => 'Semantic search is temporarily unavailable. Your other data is unaffected.',
            self::CODE_NETWORK => 'Local AI is currently unavailable. Please start Ollama and try again.',
            self::CODE_MODEL_MISSING => 'The selected local AI model is not installed in Ollama. Please run "ollama pull" for it, or set OLLAMA_MODEL in .env.',
            self::CODE_INVALID_INPUT => 'That request could not be processed.',
            default => 'The AI assistant is temporarily unavailable. Please try again shortly.',
        };
    }
}
