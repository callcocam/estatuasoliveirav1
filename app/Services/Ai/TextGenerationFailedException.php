<?php

namespace App\Services\Ai;

use RuntimeException;
use Throwable;

/**
 * Provider failure while generating text. The `reason` is a stable machine
 * code used to log the real cause and to pick the message shown in the admin;
 * `context` carries the diagnostic details that must never reach the browser.
 */
class TextGenerationFailedException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    private function __construct(
        string $message,
        public readonly string $reason,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function notConfigured(string $driver): self
    {
        return new self(
            "No API key configured for the [{$driver}] AI driver.",
            'not_configured',
            ['driver' => $driver],
        );
    }

    public static function unreachable(Throwable $previous): self
    {
        return new self(
            'The AI provider could not be reached.',
            'unreachable',
            ['detail' => $previous->getMessage()],
            $previous,
        );
    }

    public static function badStatus(int $status, string $body = ''): self
    {
        return new self(
            "The AI provider responded with HTTP {$status}.",
            self::reasonForStatus($status),
            ['status' => $status, 'body' => mb_substr($body, 0, 500)],
        );
    }

    public static function emptyResponse(string $body = ''): self
    {
        return new self(
            'The AI provider returned an empty response.',
            'empty_response',
            ['body' => mb_substr($body, 0, 500)],
        );
    }

    /**
     * Map the provider status to an actionable reason. 404 usually means the
     * configured model was retired by the provider, which is the most common
     * way this integration breaks without any code change.
     */
    private static function reasonForStatus(int $status): string
    {
        return match ($status) {
            401, 403 => 'unauthorized',
            404 => 'model_not_found',
            429 => 'rate_limited',
            default => 'bad_status',
        };
    }
}
