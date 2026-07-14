<x-mail::message>
# Je voorbeeldwebsite staat klaar

Hoi {{ $lead->contact_name ?: 'daar' }},

Je hebt zojuist een voorbeeld van {{ $lead->company ? 'de website van ' . $lead->company : 'je website' }} opgeslagen. Via de knop hieronder kom je er altijd bij terug, ook later. Bewaar deze mail, dit is jouw persoonlijke link.

<x-mail::button :url="$revisitUrl">
Bekijk je voorbeeld
</x-mail::button>

Wat je hier kunt doen:

- Je voorbeeld opnieuw bekijken wanneer je wilt
- Meerdere versies opslaan en je favoriet markeren
- Ons laten weten dat we hem voor je mogen uitwerken

Zullen we hem samen afmaken? Antwoord gewoon op deze mail, dan pakken we het op.

Groet,
Het team van Beter Geregeld

<x-slot:subcopy>
Wil je geen herinneringen meer ontvangen? Dat kan via [deze afmeldlink]({{ $afmeldUrl }}).
</x-slot:subcopy>
</x-mail::message>
