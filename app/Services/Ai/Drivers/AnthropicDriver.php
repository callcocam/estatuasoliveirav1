<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AnthropicDriver extends HttpTextDriver
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
            ->withHeaders([
                'x-api-key' => $this->key,
                'anthropic-version' => '2023-06-01',
            ])
            ->post("{$this->baseUrl}/messages", [
                'model' => $this->model,
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
    }

    protected function textPath(): string
    {
        return 'content.0.text';
    }

    protected function apiKey(): ?string
    {
        return $this->key;
    }
}
