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
    // Site-blokken → preview in de huisstijl van die site; blueprint-blokken neutraal.
    $siteKey    = $r->channel_site_id ? optional($r->site)->key : null;
    $url        = url('/blok-voorbeeld/' . $type) . ($siteKey ? '?site=' . $siteKey : '');
    $hasFields  = in_array($type, ['groeipad', 'wizard'], true) === false;
@endphp
<div style="position:relative;overflow:hidden;border-radius:10px;border:1px solid rgba(0,0,0,.08);min-height:180px;background:#fff;{{ $enabled ? '' : 'opacity:.5' }}">
    {{-- template-achtergrond --}}
    @if ($hasFields)
        <div style="position:absolute;inset:0;opacity:.38;pointer-events:none">
            <iframe src="{{ $url }}" loading="lazy" tabindex="-1"
                    style="width:1100px;height:620px;border:0;transform:scale(.92);transform-origin:top left"></iframe>
        </div>
    @endif
    {{-- witte sluier: links sterk (leesbare tekst), naar rechts lichter (template zichtbaar) --}}
    <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(255,255,255,.92),rgba(255,255,255,.5) 48%,rgba(255,255,255,.22));pointer-events:none"></div>
    {{-- voorgrond: de instellingen --}}
    <div style="position:relative;padding:16px 18px;display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;min-height:180px">
        <span style="font-weight:700;font-size:15px;color:#0f172a">{{ $typeLabel }}</span>
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
