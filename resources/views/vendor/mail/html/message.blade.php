@php($company = \App\Support\CompanyProfile::toArray())
@php($companyWhatsappUrl = \App\Support\CompanyProfile::whatsappUrl(__('app.mail.whatsapp_prefill', ['company' => $company['name']])))
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ \App\Support\CompanyProfile::absoluteLogoUrl() }}" class="logo" alt="{{ $company['name'] }}">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
**{{ $company['name'] }}**
@if ($company['address'])
{{ $company['address'] }}
@endif

@if ($company['phone']){{ $company['phone'] }}@endif @if ($company['phone'] && $company['email']) · @endif @if ($company['email'])[{{ $company['email'] }}](mailto:{{ $company['email'] }})@endif @if ($companyWhatsappUrl) · [WhatsApp]({{ $companyWhatsappUrl }})@endif


© {{ date('Y') }} {{ $company['name'] }}. {{ __('app.mail.all_rights_reserved') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
