<?php

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the contact page', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('site/Contact'));
});

it('stores a contact message and notifies the company email', function () {
    Mail::fake();
    Setting::set('contact_email', 'contato@estatuasoliveira.com.br');

    $this->from(route('contact'))
        ->post(route('contact.store'), [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '(51) 99973-2078',
            'subject' => 'Orçamento',
            'message' => 'Gostaria de um orçamento para uma fonte.',
        ])
        ->assertRedirect(route('contact'));

    $message = ContactMessage::query()->sole();

    expect($message->name)->toBe('Maria Silva')
        ->and($message->email)->toBe('maria@example.com');

    Mail::assertQueued(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->contactMessage->is($message)
        && $mail->hasTo('contato@estatuasoliveira.com.br'));

    Mail::assertQueued(ContactMessageConfirmation::class, fn (ContactMessageConfirmation $mail) => $mail->contactMessage->is($message)
        && $mail->hasTo('maria@example.com'));
});

it('sends the visitor confirmation even without a company contact email configured', function () {
    Mail::fake();

    $this->from(route('contact'))
        ->post(route('contact.store'), [
            'name' => 'João Souza',
            'email' => 'joao@example.com',
            'message' => 'Quero um orçamento de vaso grande.',
        ])
        ->assertRedirect(route('contact'));

    Mail::assertNotQueued(ContactMessageReceived::class);
    Mail::assertQueued(ContactMessageConfirmation::class, fn (ContactMessageConfirmation $mail) => $mail->hasTo('joao@example.com'));
});

it('validates the contact form', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), ['name' => '', 'email' => 'não-é-email', 'message' => ''])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactMessage::query()->count())->toBe(0);
});

it('silently discards submissions that fill the honeypot field', function () {
    Mail::fake();
    Setting::set('contact_email', 'contato@estatuasoliveira.com.br');

    $this->from(route('contact'))
        ->post(route('contact.store'), [
            'name' => 'Robô',
            'email' => 'bot@example.com',
            'message' => 'spam',
            'website' => 'https://spam.example',
        ])
        ->assertRedirect(route('contact'));

    expect(ContactMessage::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits repeated contact submissions', function () {
    Mail::fake();

    foreach (range(1, 5) as $i) {
        $this->post(route('contact.store'), [
            'name' => "Visitante {$i}",
            'email' => "visitante{$i}@example.com",
            'message' => 'Gostaria de um orçamento.',
        ])->assertRedirect();
    }

    $this->post(route('contact.store'), [
        'name' => 'Visitante 6',
        'email' => 'visitante6@example.com',
        'message' => 'Mais uma mensagem.',
    ])->assertTooManyRequests();
});

it('prefills the contact form with product data when a product slug is given', function () {
    $product = Product::factory()->published()->create([
        'name' => 'Buda',
        'slug' => 'buda-002',
        'reference' => '002',
    ]);

    $productLabel = __('app.site.contact.product_label', ['name' => 'Buda', 'reference' => '002']);

    $this->get(route('contact', ['produto' => 'buda-002']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Contact')
            ->where('prefill.subject', __('app.site.contact.product_subject', ['product' => $productLabel]))
            ->where('prefill.message', __('app.site.contact.product_message', [
                'product' => $productLabel,
                'url' => route('products.show', $product),
            ])));
});

it('does not prefill the contact form for unknown or unpublished products', function () {
    Product::factory()->create(['slug' => 'peca-rascunho']);

    $this->get(route('contact', ['produto' => 'peca-rascunho']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Contact')
            ->where('prefill', null));

    $this->get(route('contact', ['produto' => 'nao-existe']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Contact')
            ->where('prefill', null));
});
