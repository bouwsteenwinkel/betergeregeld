{{-- Favicon-set van een kanaal. Eén bron voor channels.layout én de losstaande
     verkooppagina's, zodat home en de rest van de site niet uit elkaar lopen.

     Twee paden:
     1. Het kanaal heeft een eigen set in public/channel-media/<key>/ (favicon.svg
        + favicon.ico + apple-touch-icon.png). Dan linken we die.
     2. Geen set → het gegenereerde monogram als data-URI, zoals voorheen.

     Let op: linken naar het brede logo.webp is géén optie. Een wordmark van 3:1
     met tekst wordt in een tab van 16px een onleesbare vlek, en Safari op macOS
     ondersteunt webp als favicon niet overal. --}}
@if ($site->hasIconSet())
	{{-- SVG eerst: moderne browsers pakken die en schalen scherp mee op elk dpi.
	     De .ico blijft staan voor oudere browsers en voor de bladwijzerbalk. --}}
	<link rel="icon" href="{{ $site->mediaUrl('favicon.svg') }}" type="image/svg+xml">
	<link rel="icon" href="{{ $site->mediaUrl('favicon.ico') }}" sizes="32x32">
	<link rel="apple-touch-icon" href="{{ $site->mediaUrl('apple-touch-icon.png') }}">
	<link rel="manifest" href="{{ $site->mediaUrl('site.webmanifest') }}">
@else
	@php
		$ft = $site->theme();
		$favSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="7" fill="' . $ft['primary'] . '"/><text x="16" y="23" font-family="Arial,Helvetica,sans-serif" font-size="19" font-weight="800" text-anchor="middle" fill="' . $ft['accent'] . '">' . e(mb_substr($site->monogram(), 0, 1)) . '</text></svg>';
	@endphp
	<link rel="icon" href="data:image/svg+xml,{{ rawurlencode($favSvg) }}">
	<link rel="apple-touch-icon" href="data:image/svg+xml,{{ rawurlencode($favSvg) }}">
@endif
