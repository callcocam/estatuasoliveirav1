<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Chat Completions driver for OpenAI and OpenAI-compatible providers (Groq,
 * or any host reachable via a custom base_url).
 */
class OpenAiCompatibleDriver extends HttpTextDriver
{
    public function __construct(
        private readonly string $key,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    protected function send(string $prompt): Response
    {
        return Http::timeout($this->timeout)
            ->withToken($this->key)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
    }

    protected function textPath(): string
    {
        return 'choices.0.message.content';
    }
}
