<x-mail::message>
# {{ __('app.site.contact.mail_heading') }}

**{{ __('app.site.contact.name') }}:** {{ $contactMessage->name }}

**{{ __('app.site.contact.email') }}:** {{ $contactMessage->email }}

@if ($contactMessage->phone)
**{{ __('app.site.contact.phone') }}:** {{ $contactMessage->phone }}
@endif

@if ($contactMessage->subject)
**{{ __('app.site.contact.subject') }}:** {{ $contactMessage->subject }}
@endif

**{{ __('app.site.contact.message') }}:**

{{ $contactMessage->message }}
</x-mail::message>
