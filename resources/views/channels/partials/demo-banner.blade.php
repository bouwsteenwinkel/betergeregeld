@php /** @var \App\Support\ChannelSite $site */ @endphp
{{-- Framing-balk boven de demolaag: maakt duidelijk dat dit een VOORBEELD is
     ("zo zou jouw site eruitzien"), geen echt bedrijf. Verwijst terug naar de
     verkooppagina en naar de gratis-voorbeeld-funnel. --}}
<div style="background:color-mix(in srgb,var(--c-primary) 10%,var(--c-bg));border-bottom:1px solid color-mix(in srgb,var(--c-primary) 22%,transparent)">
    <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.7rem 22px">
        <div style="display:flex;align-items:center;gap:.6rem;min-width:0">
            <span aria-hidden="true" style="font-size:1.1rem">🔍</span>
            <span style="font-size:.92rem;line-height:1.35;color:var(--c-ink)">
                <strong>Voorbeeld:</strong> zo zou de site van een {{ mb_strtolower($site->name()) }} eruit kunnen zien.
                <span class="muted">Dit is geen echt bedrijf.</span>
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;flex:0 0 auto">
            <a href="{{ $site->url('') }}" style="font-weight:600;font-size:.9rem;color:var(--c-primary)">&larr; Terug</a>
            <a href="{{ $site->url('') }}#gratis-voorbeeld" class="btn" style="padding:.55rem 1.1rem;font-size:.9rem">Vraag jouw voorbeeld aan</a>
        </div>
    </div>
</div>
