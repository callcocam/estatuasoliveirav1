<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

it('brands the email verification notification with the company name', function () {
    Setting::set('company_name', 'Estátuas Oliveira');

    $user = User::factory()->unverified()->create(['name' => 'Maria Silva']);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->getKey(),
        'hash' => sha1($user->getEmailForVerification()),
    ]);

    $mail = (new VerifyEmail)->toMail($user);

    expect($mail->subject)->toBe(__('app.mail.verify.subject', ['company' => 'Estátuas Oliveira']))
        ->and($mail->greeting)->toBe(__('app.mail.greeting', ['name' => 'Maria Silva']))
        ->and($mail->actionText)->toBe(__('app.mail.verify.action'))
        ->and($mail->salutation)->toBe(__('app.mail.salutation', ['company' => 'Estátuas Oliveira']));

    expect($url)->not->toBeNull();
});

it('brands the password reset notification with the company name', function () {
    Setting::set('company_name', 'Estátuas Oliveira');

    $user = User::factory()->create(['name' => 'Maria Silva']);

    $mail = (new ResetPassword('fake-token'))->toMail($user);

    expect($mail->subject)->toBe(__('app.mail.reset.subject', ['company' => 'Estátuas Oliveira']))
        ->and($mail->greeting)->toBe(__('app.mail.greeting', ['name' => 'Maria Silva']))
        ->and($mail->actionText)->toBe(__('app.mail.reset.action'))
        ->and($mail->actionUrl)->toContain('fake-token')
        ->and($mail->salutation)->toBe(__('app.mail.salutation', ['company' => 'Estátuas Oliveira']));
});
