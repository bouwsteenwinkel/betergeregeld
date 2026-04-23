@php
	$locale = app()->getLocale();
	$appUrl = rtrim(config('app.url'), '/');
	$unsubUrl = $appUrl . '/' . $locale . '/accessguard/notifications/unsubscribe/' . $unsubscribeToken;
	$risksLink = $appUrl . '/' . $locale . '/tools/accessguard/risicos';
@endphp
<x-mail::message>
# 🚨 Kritiek AccessGuard risico

**{{ $flag->title }}**

{{ $flag->description }}

**Ernst:** {{ $flag->severity }}/5 · **Type:** `{{ $flag->kind }}`

<x-mail::button :url="$risksLink" color="error">Open risico's in AccessGuard</x-mail::button>

Dit is een automatische melding omdat AccessGuard dit als kritiek risico heeft gedetecteerd (severity 5). Losse sev-5-notificaties kun je uitzetten in je notificatie-voorkeuren.

<x-slot:subcopy>
[Uitschrijven van kritieke alerts]({{ $unsubUrl }}) · Gedetecteerd: {{ $flag->detected_at->format('d-m-Y H:i') }}
</x-slot:subcopy>
</x-mail::message>
