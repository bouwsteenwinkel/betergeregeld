@php
    /** @var \App\Support\ChannelSite $site */
    // Logo-systeem (per site genereerbaar):
    //   1. brand.logo_image  -> eigen geüpload beeld wint altijd.
    //   2. brand.logo_mark   -> een crafted SVG-embleem (bv. 'barber').
    //   3. anders             -> auto-monogram-badge uit de sitenaam (themakleuren).
    // Daarnaast: wordmark in de display-font + optionele brand.logo_tagline.
    $mark    = $site->brand('logo_mark');
    $tagline = $site->brand('logo_tagline');
@endphp
@if ($site->logoImage())
    <img src="{{ $site->logoImage() }}" alt="{{ $site->name() }}" style="height:40px;display:block">
@else
    <span class="logo-mark" aria-hidden="true">
        @if ($mark === 'barber')
            {{-- Barber-pole keurmerk-badge: ronde charcoal-badge met goud/crème pole. --}}
            <svg width="44" height="44" viewBox="0 0 48 48" fill="none">
                <defs><clipPath id="poleClip"><rect x="19" y="15" width="10" height="18" rx="5"/></clipPath></defs>
                <circle cx="24" cy="24" r="23" fill="#1f1b18"/>
                <circle cx="24" cy="24" r="22.25" fill="none" stroke="#c79a3a" stroke-width="1.5"/>
                <circle cx="24" cy="24" r="18.5" fill="none" stroke="#c79a3a" stroke-width="1" stroke-opacity=".35"/>
                {{-- caps + finials --}}
                <circle cx="24" cy="10.4" r="1.7" fill="#efe4c8"/>
                <circle cx="24" cy="37.6" r="1.7" fill="#efe4c8"/>
                <rect x="16.5" y="12" width="15" height="3.1" rx="1.55" fill="#c79a3a"/>
                <rect x="16.5" y="32.9" width="15" height="3.1" rx="1.55" fill="#c79a3a"/>
                {{-- pole body + diagonale strepen --}}
                <rect x="19" y="15" width="10" height="18" rx="5" fill="#2a241f"/>
                <g clip-path="url(#poleClip)">
                    <g transform="rotate(34 24 24)">
                        <rect x="6"  y="0" width="4" height="48" fill="#efe4c8"/>
                        <rect x="10" y="0" width="4" height="48" fill="#c79a3a"/>
                        <rect x="14" y="0" width="4" height="48" fill="#efe4c8"/>
                        <rect x="18" y="0" width="4" height="48" fill="#c79a3a"/>
                        <rect x="22" y="0" width="4" height="48" fill="#efe4c8"/>
                        <rect x="26" y="0" width="4" height="48" fill="#c79a3a"/>
                        <rect x="30" y="0" width="4" height="48" fill="#efe4c8"/>
                        <rect x="34" y="0" width="4" height="48" fill="#c79a3a"/>
                        <rect x="38" y="0" width="4" height="48" fill="#efe4c8"/>
                    </g>
                </g>
                <rect x="19" y="15" width="10" height="18" rx="5" fill="none" stroke="#c79a3a" stroke-width="1"/>
            </svg>
        @elseif ($mark === 'nails')
            {{-- Nagellak-fles-badge: themakleuren-badge met een fles in de CTA-kleur. --}}
            <svg width="44" height="44" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="23" fill="var(--c-primary)"/>
                <circle cx="24" cy="24" r="22.25" fill="none" stroke="var(--c-accent)" stroke-width="1.5"/>
                <circle cx="24" cy="24" r="18.5" fill="none" stroke="var(--c-accent)" stroke-width="1" stroke-opacity=".35"/>
                <rect x="20" y="9" width="8" height="12" rx="2.5" fill="var(--c-accent)"/>
                <rect x="21.5" y="20" width="5" height="3" fill="#efe4d6" opacity=".85"/>
                <rect x="17.5" y="22.5" width="13" height="14" rx="3.5" fill="var(--c-cta)"/>
                <rect x="20" y="25.5" width="3" height="7" rx="1.5" fill="#ffffff" opacity=".28"/>
            </svg>
        @else
            {{-- Auto-monogram-badge: initialen uit de naam, in de themakleuren. --}}
            <svg width="44" height="44" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="23" fill="var(--c-primary)"/>
                <circle cx="24" cy="24" r="22.25" fill="none" stroke="var(--c-accent)" stroke-width="1.5"/>
                <circle cx="24" cy="24" r="18.5" fill="none" stroke="var(--c-accent)" stroke-width="1" stroke-opacity=".35"/>
                <text x="24" y="24.5" text-anchor="middle" dominant-baseline="central"
                      font-family="var(--font-display)" font-weight="700" font-size="17" letter-spacing="0.5"
                      fill="#ffffff">{{ $site->monogram() }}</text>
            </svg>
        @endif
    </span>
    <span class="logo-text">
        <span class="logo-word">{!! $site->brand('logo_text', $site->name()) !!}</span>
        @if ($tagline)<span class="logo-tag">{{ $tagline }}</span>@endif
    </span>
@endif
