{{-- Strak monochroom lijn-icoon (SVG, currentColor) voor het inbegrepen-blok.
     Verwacht $icon (slug uit config/included_services.php). Geen emoji: die
     ogen "AI-achtig"; deze lijn-iconen passen bij de huisstijl. --}}
@php
    $__icons = [
        'server'   => '<rect x="3" y="4" width="18" height="7" rx="1.5"/><rect x="3" y="13" width="18" height="7" rx="1.5"/><path d="M6.5 7.5h.01"/><path d="M6.5 16.5h.01"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>',
        'shield'   => '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
        'save'     => '<path d="M5 3h11l3 3v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M8 3v5h7V3"/><rect x="8" y="13" width="8" height="6"/>',
        'wrench'   => '<path d="M20 12a8 8 0 1 1-2.34-5.66"/><path d="M20 4v4h-4"/>',
        'search'   => '<circle cx="10.5" cy="10.5" r="6"/><path d="M15 15l5 5"/>',
        'globe'    => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17"/><path d="M12 3.5c2.4 2.6 2.4 14.4 0 17"/><path d="M12 3.5c-2.4 2.6-2.4 14.4 0 17"/>',
        'link'     => '<path d="M9 15l6-6"/><path d="M10.5 6.8l1.3-1.3a3.5 3.5 0 0 1 5 5l-1.5 1.5"/><path d="M13.5 17.2l-1.3 1.3a3.5 3.5 0 0 1-5-5l1.5-1.5"/>',
        'bolt'     => '<path d="M13 3L5 13h5l-1 8 8-11h-5l1-7z"/>',
        'language' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.4h.01"/><path d="M7.2 10.4h9.6"/><path d="M10 10.4l1 4-1 3.6"/><path d="M14 10.4l-1 4 1 3.6"/>',
        'default'  => '<circle cx="12" cy="12" r="8.5"/><path d="M9 12l2 2 4-4"/>',
    ];
    $__svg = $__icons[$icon ?? 'default'] ?? $__icons['default'];
@endphp
<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $__svg !!}</svg>
