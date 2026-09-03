<x-mail::message>
# New message from your portfolio

**From:** {{ $contactMessage->name }} &lt;{{ $contactMessage->email }}&gt;

@if ($contactMessage->subject)
**Subject:** {{ $contactMessage->subject }}
@endif

**Received:** {{ $contactMessage->created_at?->format('j M Y, g:ia') }}

---

{{ $contactMessage->message }}

---

<x-mail::button :url="route('admin.messages.show', $contactMessage)">
Open in admin panel
</x-mail::button>

You can reply to this email directly to reach {{ $contactMessage->name }}.
</x-mail::message>
