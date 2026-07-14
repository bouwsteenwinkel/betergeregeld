<x-mail::message>
# {{ $kind === 'dag4' ? 'Nog een korte herinnering' : 'Je voorbeeld wacht nog op je' }}

Hoi {{ $lead->contact_name ?: 'daar' }},

@if ($kind === 'dag4')
Een paar dagen geleden maakte je een voorbeeld van {{ $lead->company ? 'de website van ' . $lead->company : 'je website' }}. Het staat er nog, klaar om af te maken. Zal ik je laten zien hoe de definitieve versie eruit kan zien?
@else
Je maakte gisteren een voorbeeld van {{ $lead->company ? 'de website van ' . $lead->company : 'je website' }}. Wil je hem nog eens bekijken of samen verder uitwerken?
@endif

<x-mail::button :url="$revisitUrl">
Bekijk je voorbeeld
</x-mail::button>

Antwoord gerust op deze mail met je vragen, dan denken we vrijblijvend met je mee.

Groet,
Het team van Beter Geregeld

<x-slot:subcopy>
Liever geen herinneringen meer? [Meld je hier af]({{ $afmeldUrl }}).
</x-slot:subcopy>
</x-mail::message>
