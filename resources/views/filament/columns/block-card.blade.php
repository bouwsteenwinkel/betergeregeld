@php
    use App\Models\Channel\Block;
    $r          = $getRecord();
    $type       = $r->type;
    $typeLabel  = Block::TYPES[$type] ?? $type;
    $statusKey  = $r->status ?? 'placeholder';
    $statusLbl  = Block::STATUSES[$statusKey] ?? $statusKey;
    $statusCol  = match ($statusKey) { 'klaar' => '#16a34a', 'bewerking' => '#d97706', default => '#94a3b8' };
    $blockKey   = $r->block_key ?? null;
    $enabled    = $r->enabled ?? true;
    $facetKey   = $r->facet ?? null;
    $facetLbl   = $facetKey ? (config('groeidiamant.facets.' . $facetKey . '.label') ?? $facetKey) : 'Basis';
    // Site-blokken → preview in de huisstijl van die site; blueprint-blokken neutraal.
    $siteKey    = $r->channel_site_id ? optional($r->site)->key : null;
    $url        = url('/blok-voorbeeld/' . $type) . ($siteKey ? '?site=' . $siteKey : '');
    $hasFields  = in_array($type, ['groeipad', 'wizard'], true) === false;
@endphp
<div style="position:relative;width:100%;overflow:hidden;border-radius:10px;border:1px solid rgba(0,0,0,.08);min-height:200px;background:#fff;{{ $enabled ? '' : 'opacity:.5' }}">
    {{-- template-achtergrond, volle breedte --}}
    @if ($hasFields)
        <div style="position:absolute;inset:0;opacity:.55;pointer-events:none">
            <iframe src="{{ $url }}" loading="lazy" tabindex="-1"
                    style="width:100%;height:700px;border:0;display:block"></iframe>
        </div>
    @endif
    {{-- lichte sluier: links iets sterker voor leesbare tekst, rechts vrijwel transparant --}}
    <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(255,255,255,.86),rgba(255,255,255,.35) 40%,rgba(255,255,255,.08));pointer-events:none"></div>
    {{-- voorgrond: de instellingen --}}
    <div style="position:relative;padding:16px 18px;display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;min-height:200px">
        <span style="font-weight:700;font-size:15px;color:#0f172a">{{ $typeLabel }}</span>
        <span style="font-size:11px;font-weight:600;color:{{ $facetKey ? '#fff' : '#475569' }};background:{{ $facetKey ? '#6366f1' : '#e2e8f0' }};padding:2px 9px;border-radius:999px">{{ $facetLbl }}</span>
        <span style="font-size:11px;font-weight:600;color:#fff;background:{{ $statusCol }};padding:2px 9px;border-radius:999px">{{ $statusLbl }}</span>
        @if ($r->locked)
            <span title="Funnel — niet verwijderbaar" style="font-size:12px;font-weight:600;color:#b45309">🔒 funnel</span>
        @endif
        @if ($blockKey && $blockKey !== $type)
            <span style="font-size:12px;color:#94a3b8;font-family:monospace">{{ $blockKey }}</span>
        @endif
        @unless ($enabled)
            <span style="font-size:11px;font-weight:600;color:#fff;background:#94a3b8;padding:2px 9px;border-radius:999px">Verborgen</span>
        @endunless
    </div>
</div>
