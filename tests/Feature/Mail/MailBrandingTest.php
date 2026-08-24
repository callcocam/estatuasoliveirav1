<?php

use App\Mail\ContactMessageConfirmation;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

it('renders the branded layout with the company data from the settings', function () {
    Setting::set('company_name', 'Estátuas Oliveira');
    Setting::set('contact_email', 'contato@estatuasoliveira.com.br');
    Setting::set('contact_phone', '(51) 3563-0000');
    Setting::set('contact_whatsapp', '51999732078');

    $contactMessage = ContactMessage::factory()->create([
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'message' => 'Gostaria de um orçamento.',
    ]);

    $html = (new ContactMessageConfirmation($contactMessage))->render();

    expect($html)
        ->toContain('Estátuas Oliveira')
        ->toContain('contato@estatuasoliveira.com.br')
        ->toContain('(51) 3563-0000')
        ->toContain('https://wa.me/5551999732078')
        ->toContain(__('app.mail.all_rights_reserved'));
});

it('uses the company name from the settings as the sender display name', function () {
    Setting::set('company_name', 'Estátuas Oliveira');

    $contactMessage = ContactMessage::factory()->create();

    Mail::to('maria@example.com')->send(new ContactMessageConfirmation($contactMessage));

    $messages = app('mailer')->getSymfonyTransport()->messages();

    expect($messages)->toHaveCount(1)
        ->and($messages[0]->getOriginalMessage()->getFrom()[0]->getName())->toBe('Estátuas Oliveira');
});

it('confirms the visitor message with a copy and a whatsapp shortcut', function () {
    Setting::set('company_name', 'Estátuas Oliveira');
    Setting::set('contact_whatsapp', '51999732078');

    $contactMessage = ContactMessage::factory()->create([
        'name' => 'Maria Silva',
        'subject' => 'Orçamento de fonte',
        'message' => 'Gostaria de um orçamento para uma fonte.',
    ]);

    $mailable = new ContactMessageConfirmation($contactMessage);

    $mailable->assertHasSubject(__('app.site.contact.confirmation_subject', ['company' => 'Estátuas Oliveira']));

    $html = $mailable->render();

    expect($html)
        ->toContain('Orçamento de fonte')
        ->toContain('Gostaria de um orçamento para uma fonte.')
        ->toContain(__('app.site.contact.confirmation_whatsapp_action'));
});
