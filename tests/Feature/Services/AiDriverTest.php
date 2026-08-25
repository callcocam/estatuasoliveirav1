<?php

use App\Services\Ai\ProductDescriptionGenerator;
use App\Services\Ai\TextGenerationFailedException;
use App\Services\Ai\TextGenerator;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('gemini driver sends the expected request and parses the text', function () {
    config()->set('ai.driver', 'gemini');
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => ' Descrição gerada. ']]]]],
        ]),
    ]);

    expect(app(TextGenerator::class)->generate('meu prompt'))->toBe('Descrição gerada.');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent'
        && $request->hasHeader('x-goog-api-key', 'gemini-test-key')
        && $request['contents'][0]['parts'][0]['text'] === 'meu prompt');
});

test('groq driver uses the openai-compatible chat completions endpoint', function () {
    config()->set('ai.driver', 'groq');
    config()->set('ai.drivers.groq.key', 'groq-test-key');

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'Texto do Groq']]],
        ]),
    ]);

    expect(app(TextGenerator::class)->generate('meu prompt'))->toBe('Texto do Groq');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer groq-test-key')
        && $request['model'] === 'llama-3.3-70b-versatile'
        && $request['messages'][0]['content'] === 'meu prompt');
});

test('openai driver targets the openai api with the configured model', function () {
    config()->set('ai.driver', 'openai');
    config()->set('ai.drivers.openai.key', 'openai-test-key');

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => 'Texto do OpenAI']]],
        ]),
    ]);

    expect(app(TextGenerator::class)->generate('meu prompt'))->toBe('Texto do OpenAI');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.openai.com/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer openai-test-key')
        && $request['model'] === 'gpt-4o-mini');
});

test('ollama driver calls the local chat endpoint without auth', function () {
    config()->set('ai.driver', 'ollama');

    Http::fake([
        'localhost:11434/*' => Http::response([
            'message' => ['content' => 'Texto local'],
        ]),
    ]);

    expect(app(TextGenerator::class)->generate('meu prompt'))->toBe('Texto local');

    Http::assertSent(fn ($request): bool => $request->url() === 'http://localhost:11434/api/chat'
        && $request['model'] === 'llama3.2'
        && $request['stream'] === false);
});

test('anthropic driver sends the versioned messages request', function () {
    config()->set('ai.driver', 'anthropic');
    config()->set('ai.drivers.anthropic.key', 'anthropic-test-key');

    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Texto do Claude']],
        ]),
    ]);

    expect(app(TextGenerator::class)->generate('meu prompt'))->toBe('Texto do Claude');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.anthropic.com/v1/messages'
        && $request->hasHeader('x-api-key', 'anthropic-test-key')
        && $request->hasHeader('anthropic-version', '2023-06-01')
        && $request['model'] === 'claude-haiku-4-5-20251001'
        && $request['messages'][0]['content'] === 'meu prompt');
});

test('provider http errors raise a text generation exception', function () {
    config()->set('ai.driver', 'gemini');
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => 'quota'], 500),
    ]);

    app(TextGenerator::class)->generate('meu prompt');
})->throws(TextGenerationFailedException::class);

test('a missing api key fails before any request is sent', function () {
    config()->set('ai.driver', 'gemini');
    config()->set('ai.drivers.gemini.key', '');

    expect(fn () => app(TextGenerator::class)->generate('meu prompt'))
        ->toThrow(TextGenerationFailedException::class);

    Http::assertNothingSent();
});

test('provider statuses map to actionable reasons', function (int $status, string $reason) {
    config()->set('ai.driver', 'gemini');
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => 'boom'], $status),
    ]);

    try {
        app(TextGenerator::class)->generate('meu prompt');
    } catch (TextGenerationFailedException $exception) {
        expect($exception->reason)->toBe($reason);

        return;
    }

    $this->fail('The driver did not raise a text generation exception.');
})->with([
    [401, 'unauthorized'],
    [404, 'model_not_found'],
    [429, 'rate_limited'],
    [500, 'bad_status'],
]);

test('empty provider responses raise a text generation exception', function () {
    config()->set('ai.driver', 'gemini');
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['candidates' => []]),
    ]);

    app(TextGenerator::class)->generate('meu prompt');
})->throws(TextGenerationFailedException::class);

test('product description prompt only includes informed facts', function () {
    $spy = new class implements TextGenerator
    {
        public ?string $prompt = null;

        public function generate(string $prompt): string
        {
            $this->prompt = $prompt;

            return 'ok';
        }
    };

    (new ProductDescriptionGenerator($spy))->generate([
        'name' => 'Buda meditando',
        'category' => 'Estátuas',
        'width_cm' => 40,
    ]);

    expect($spy->prompt)
        ->toContain('Nome do produto: Buda meditando')
        ->toContain('Categoria: Estátuas')
        ->toContain('Largura: 40 cm')
        ->not->toContain('Peso')
        ->not->toContain('Altura');
});
