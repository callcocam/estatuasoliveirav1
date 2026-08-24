<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Support\CompanyProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.site.contact.confirmation_subject', ['company' => CompanyProfile::name()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-message-confirmation',
            with: [
                'contactMessage' => $this->contactMessage,
                'company' => CompanyProfile::toArray(),
                'whatsappUrl' => CompanyProfile::whatsappUrl(
                    __('app.mail.whatsapp_prefill', ['company' => CompanyProfile::name()]),
                ),
            ],
        );
    }
}
