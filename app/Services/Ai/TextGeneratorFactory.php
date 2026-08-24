<?php

namespace App\Services\Ai;

use App\Services\Ai\Drivers\AnthropicDriver;
use App\Services\Ai\Drivers\GeminiDriver;
use App\Services\Ai\Drivers\OllamaDriver;
use App\Services\Ai\Drivers\OpenAiCompatibleDriver;
use InvalidArgumentException;

class TextGeneratorFactory
{
    /**
     * Build the text generator for the driver configured in config/ai.php.
     */
    public static function make(): TextGenerator
    {
        $driver = (string) config('ai.driver');

        /** @var array{key: string|null, model: string, base_url: string} $config */
        $config = (array) config("ai.drivers.{$driver}", []);
        $timeout = (int) config('ai.timeout');

        return match ($driver) {
            'gemini' => new GeminiDriver(
                key: (string) ($config['key'] ?? ''),
                model: $config['model'],
                baseUrl: $config['base_url'],
                timeout: $timeout,
            ),
            'groq', 'openai' => new OpenAiCompatibleDriver(
                key: (string) ($config['key'] ?? ''),
                model: $config['model'],
                baseUrl: $config['base_url'],
                timeout: $timeout,
            ),
            'ollama' => new OllamaDriver(
                model: $config['model'],
                baseUrl: $config['base_url'],
                timeout: $timeout,
            ),
            'anthropic' => new AnthropicDriver(
                key: (string) ($config['key'] ?? ''),
                model: $config['model'],
                baseUrl: $config['base_url'],
                timeout: $timeout,
            ),
            default => throw new InvalidArgumentException("Unsupported AI driver [{$driver}]."),
        };
    }
}
