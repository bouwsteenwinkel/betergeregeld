@php
    /**
     * Zelf-getekende, flat-illustratie avatar (geen nep-foto's). Parametrisch zodat
     * elk teamlid uniek is: huidskleur, haar, baard, lang haar, kleding, achtergrond.
     * $av = ['skin','hair','beard'(of null),'bg','shirt','long'(bool),'id','alt'].
     */
    $skin  = $av['skin']  ?? '#e8b48f';
    $hair  = $av['hair']  ?? '#2b2018';
    $beard = $av['beard'] ?? null;
    $bg    = $av['bg']    ?? '#f1e7d4';
    $shirt = $av['shirt'] ?? '#1f1b18';
    $long  = ! empty($av['long']);
    $id    = $av['id']    ?? 'av';
@endphp
<svg class="avatar-svg" viewBox="0 0 120 120" role="img" aria-label="{{ $av['alt'] ?? 'Teamlid' }}">
    <defs><clipPath id="clip-{{ $id }}"><circle cx="60" cy="60" r="60"/></clipPath></defs>
    <g clip-path="url(#clip-{{ $id }})">
        <rect width="120" height="120" fill="{{ $bg }}"/>
        @if ($long)<ellipse cx="60" cy="61" rx="31" ry="33" fill="{{ $hair }}"/>@endif
        <ellipse cx="60" cy="124" rx="42" ry="30" fill="{{ $shirt }}"/>
        <rect x="51" y="71" width="18" height="22" rx="8" fill="{{ $skin }}"/>
        @unless ($long)<circle cx="60" cy="49" r="27" fill="{{ $hair }}"/>@endunless
        <circle cx="60" cy="56" r="23" fill="{{ $skin }}"/>
        <circle cx="37.5" cy="58" r="4.5" fill="{{ $skin }}"/>
        <circle cx="82.5" cy="58" r="4.5" fill="{{ $skin }}"/>
        @if ($beard)
            <path d="M37 55 Q39 83 60 83 Q81 83 83 55 Q82 71 60 71 Q38 71 37 55 Z" fill="{{ $beard }}"/>
        @else
            <path d="M54 67 q6 4.5 12 0" stroke="#b5715a" stroke-width="2.2" fill="none" stroke-linecap="round"/>
        @endif
        <circle cx="52" cy="55" r="2.2" fill="#2b2018"/>
        <circle cx="68" cy="55" r="2.2" fill="#2b2018"/>
        <path d="M47.5 49.5 q4.5 -2.5 9 0" stroke="{{ $hair }}" stroke-width="2" fill="none" stroke-linecap="round"/>
        <path d="M63.5 49.5 q4.5 -2.5 9 0" stroke="{{ $hair }}" stroke-width="2" fill="none" stroke-linecap="round"/>
    </g>
</svg>
