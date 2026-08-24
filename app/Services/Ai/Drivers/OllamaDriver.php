<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OllamaDriver extends HttpTextDriver
{
    public function __construct(
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    protected function send(string $prompt): Response
    {
        return Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/chat", [
                'model' => $this->model,
                'stream' => false,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
    }

    protected function textPath(): string
    {
        return 'message.content';
    }
}
