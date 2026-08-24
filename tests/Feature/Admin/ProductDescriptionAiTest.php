<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    $this->admin = User::factory()->admin()->create();
});

function fakeGeminiDescriptionResponse(): void
{
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Descrição gerada por IA.']]]]],
        ]),
    ]);
}

test('admin generates a product description', function () {
    fakeGeminiDescriptionResponse();

    $this->actingAs($this->admin)
        ->postJson(route('admin.products.generate-description'), [
            'name' => 'Buda meditando',
            'category' => 'Estátuas',
            'width_cm' => 40,
            'height_cm' => 60,
        ])
        ->assertOk()
        ->assertJson(['description' => 'Descrição gerada por IA.']);
});

test('name is required to generate a description', function () {
    $this->actingAs($this->admin)
        ->postJson(route('admin.products.generate-description'), [
            'category' => 'Estátuas',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('customers cannot generate descriptions', function () {
    fakeGeminiDescriptionResponse();

    $this->actingAs(User::factory()->create())
        ->postJson(route('admin.products.generate-description'), ['name' => 'Buda'])
        ->assertForbidden();

    Http::assertNothingSent();
});

test('provider failure returns 422 with a translated message', function () {
    config()->set('ai.drivers.gemini.key', 'gemini-test-key');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response(['error' => 'quota'], 500),
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('admin.products.generate-description'), ['name' => 'Buda'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.description.0', __('app.admin.products.ai.failed'));
});

test('generation is throttled after ten requests per minute', function () {
    fakeGeminiDescriptionResponse();

    foreach (range(1, 10) as $attempt) {
        $this->actingAs($this->admin)
            ->postJson(route('admin.products.generate-description'), ['name' => 'Buda'])
            ->assertOk();
    }

    $this->actingAs($this->admin)
        ->postJson(route('admin.products.generate-description'), ['name' => 'Buda'])
        ->assertStatus(429);
});
