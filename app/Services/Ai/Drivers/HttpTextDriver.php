<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\TextGenerationFailedException;
use App\Services\Ai\TextGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

abstract class HttpTextDriver implements TextGenerator
{
    public function generate(string $prompt): string
    {
        try {
            $response = $this->send($prompt);
        } catch (ConnectionException $exception) {
            throw TextGenerationFailedException::unreachable($exception);
        }

        if ($response->failed()) {
            throw TextGenerationFailedException::badStatus($response->status());
        }

        $text = trim((string) $response->json($this->textPath()));

        if ($text === '') {
            throw TextGenerationFailedException::emptyResponse();
        }

        return $text;
    }

    /**
     * Perform the provider-specific HTTP request for the prompt.
     */
    abstract protected function send(string $prompt): Response;

    /**
     * Dot-notation path of the generated text inside the JSON response.
     */
    abstract protected function textPath(): string;
}
