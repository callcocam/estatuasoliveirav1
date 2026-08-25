<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiDriver extends HttpTextDriver
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
            ->withHeaders(['x-goog-api-key' => $this->key])
            ->post("{$this->baseUrl}/models/{$this->model}:generateContent", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);
    }

    protected function textPath(): string
    {
        return 'candidates.0.content.parts.0.text';
    }

    protected function apiKey(): ?string
    {
        return $this->key;
    }
}
