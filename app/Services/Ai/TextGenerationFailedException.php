<?php

namespace App\Services\Ai;

use RuntimeException;
use Throwable;

class TextGenerationFailedException extends RuntimeException
{
    public static function unreachable(Throwable $previous): self
    {
        return new self('The AI provider could not be reached.', 0, $previous);
    }

    public static function badStatus(int $status): self
    {
        return new self("The AI provider responded with HTTP {$status}.");
    }

    public static function emptyResponse(): self
    {
        return new self('The AI provider returned an empty response.');
    }
}
