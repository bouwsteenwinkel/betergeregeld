<?php

return [

	/*
	 * Wie een melding krijgt als iemand het contactformulier op betergeregeld.com invult.
	 *
	 * Tot 11-09-2026 ging er GEEN melding uit: een inzending kwam alleen in de tabel
	 * contact_messages (Filament → ContactMessages) en niemand kreeg bericht. Precies de
	 * val die bij Bouwsteenwinkel twintig onbeantwoorde aanvragen opleverde.
	 *
	 * Bewust dennis@ en niet scheduling.notify_email (info@): op dennis@ staat de
	 * Gmail-regel op "WEBFORM" in het onderwerp die deze mails naar Primair zet, met
	 * ster. info@ is een andere postbus.
	 */
	'notify_email' => env('CONTACT_NOTIFY_EMAIL', 'dennis@bouwsteenwinkel.nl'),

];
