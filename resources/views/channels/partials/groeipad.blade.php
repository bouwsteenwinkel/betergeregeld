@php
    /** @var \App\Support\ChannelSite $site */
    $facets  = $facets ?? (array) config('groeidiamant.facets', []);
    $current = $facet ?? (string) config('groeidiamant.default', 'website');
    $curNr   = (int) ($facets[$current]['nr'] ?? 1);
@endphp
@if (!empty($facets))
<section id="groeipad">
    <div class="wrap" style="text-align:center">
        <span class="eyebrow">De Groeidiamant</span>
        <h2>Jouw groeipad — stap voor stap</h2>
        <p class="muted" style="max-width:62ch;margin:.4rem auto 2rem">
            Je begint waar jij nu staat. Groeit je bedrijf, dan groeit je website gewoon mee —
            je hoeft nooit opnieuw te beginnen.
        </p>

        <div style="display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;align-items:stretch">
            @foreach ($facets as $key => $f)
                @php
                    $nr = (int) ($f['nr'] ?? 0);
                    $state = $nr < $curNr ? 'done' : ($key === $current ? 'now' : 'next');
                @endphp
                <div style="flex:1;min-width:150px;max-width:200px;border-radius:var(--radius);padding:1.1rem .9rem;text-align:left;
                    @if($state==='now') background:var(--c-primary);color:#fff;box-shadow:0 10px 30px color-mix(in srgb,var(--c-primary) 35%,transparent);
                    @elseif($state==='done') background:var(--c-surface);border:1px solid color-mix(in srgb,var(--c-accent) 45%,transparent);
                    @else background:var(--c-surface);border:1px dashed color-mix(in srgb,var(--c-muted) 40%,transparent);opacity:.7; @endif">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem">
                        <span style="font-size:1.5rem">{{ $f['icon'] ?? '' }}</span>
                        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                            @if($state==='now') color:rgba(255,255,255,.85) @elseif($state==='done') color:var(--c-accent) @else color:var(--c-muted) @endif">
                            @if($state==='done') ✓ heb je @elseif($state==='now') jouw stap @else later @endif
                        </span>
                    </div>
                    <div style="font-weight:700;font-size:1.02rem;@if($state!=='now')color:var(--c-ink)@endif">{{ $nr }}. {{ $f['label'] ?? $key }}</div>
                    <div style="font-size:.82rem;margin-top:.2rem;@if($state==='now')color:rgba(255,255,255,.85)@else color:var(--c-muted)@endif">{{ $f['tagline'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
