<x-mail::message>
@if ($brandLogo)
<img src="{{ $brandLogo }}" alt="{{ $brandName }} logo" style="height: 56px; width: auto; margin-bottom: 16px;">
@endif

# New enquiry for {{ $brandName }}

**From:** {{ $contactMessage->name }} &lt;{{ $contactMessage->email }}&gt;

@if ($contactMessage->subject)
**Subject:** {{ $contactMessage->subject }}
@endif

**Received:** {{ $contactMessage->created_at?->format('j M Y, g:ia') }}

---

{{ $contactMessage->message }}

---

<x-mail::button :url="route('messageShow', $contactMessage)">
Open in admin panel
</x-mail::button>

Reply to this email to reach {{ $contactMessage->name }} directly.

{{ $brandName }}
</x-mail::message>
