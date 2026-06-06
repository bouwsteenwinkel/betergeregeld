@php
	$locale = app()->getLocale();
	$appUrl = rtrim(config('app.url'), '/');
	$unsubUrl = $appUrl . '/' . $locale . '/accessguard/notifications/unsubscribe/' . $unsubscribeToken;
	$appLink = $appUrl . '/' . $locale . '/tools/accessguard';
	$risksLink = $appUrl . '/' . $locale . '/tools/accessguard/risicos';
	$remindersLink = $appUrl . '/' . $locale . '/tools/accessguard/reminders';
	$kindLabels = [
		'stale_admin' => 'Stale admin',
		'orphan_access' => 'Orphan access',
		'excessive_access' => 'Excessive access',
		'overdue_review' => 'Overdue review',
		'overdue_action' => 'Overdue action',
		'pending_onboarding' => 'Onboarding vergeten',
		'stale_credential' => 'Stale credential',
	];
@endphp
<x-mail::message>
# AccessGuard dagelijks overzicht

Een korte samenvatting van wat je aandacht nodig heeft in AccessGuard.

@if ($digest['open_risks_count'] > 0)
## Open risico's, {{ $digest['open_risks_count'] }} totaal

@foreach ($digest['open_risks'] as $r)
**[Sev {{ $r->severity }}] {{ $r->title }}**, {{ $kindLabels[$r->kind] ?? $r->kind }}
{{ $r->description }}

@endforeach

<x-mail::button :url="$risksLink">Bekijk alle risico's</x-mail::button>
@endif

@if ($digest['upcoming_reminders_count'] > 0)
## Reminders, {{ $digest['upcoming_reminders_count'] }} open

@foreach ($digest['upcoming_reminders'] as $rem)
**{{ $rem->title }}**
@if ($rem->due_at)({{ $rem->due_at->format('d-m-Y') }}) @endif · {{ $rem->description }}

@endforeach

<x-mail::button :url="$remindersLink">Bekijk alle reminders</x-mail::button>
@endif

@if ($digest['open_actions_count'] > 0)
**{{ $digest['open_actions_count'] }} open acties** in de acties-queue die wachten op afronding.
@endif

Bedankt,<br>
{{ config('app.name') }}

<x-slot:subcopy>
Je ontvangt dit bericht omdat je notificatie-voorkeuren dit toestaan.
[Uitschrijven van deze dagelijkse digest]({{ $unsubUrl }}) ·
[Open AccessGuard]({{ $appLink }})
</x-slot:subcopy>
</x-mail::message>
