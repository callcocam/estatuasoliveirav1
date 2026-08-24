<x-mail::message>
{{ __('app.mail.greeting', ['name' => $contactMessage->name]) }}

{{ __('app.site.contact.confirmation_intro') }}

@if ($contactMessage->subject)
**{{ __('app.site.contact.subject') }}:** {{ $contactMessage->subject }}
@endif

> {{ $contactMessage->message }}

@if ($whatsappUrl)
{{ __('app.site.contact.confirmation_whatsapp') }}

<x-mail::button :url="$whatsappUrl">
{{ __('app.site.contact.confirmation_whatsapp_action') }}
</x-mail::button>
@endif

{{ __('app.mail.salutation', ['company' => $company['name']]) }}
</x-mail::message>
