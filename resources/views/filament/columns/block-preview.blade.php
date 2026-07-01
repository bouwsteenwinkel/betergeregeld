@php
    $record  = $getRecord();
    $type    = $record->type;
    // Site-blokken hebben een site (voor de juiste huisstijl); blueprint-blokken niet.
    $siteKey = $record->channel_site_id ? optional($record->site)->key : null;
    $url     = url('/blok-voorbeeld/' . $type) . ($siteKey ? '?site=' . $siteKey : '');
@endphp
<div style="width:200px;height:84px;overflow:hidden;border-radius:6px;border:1px solid rgba(0,0,0,.08);background:#fff;pointer-events:none">
    <iframe src="{{ $url }}" loading="lazy" tabindex="-1"
            style="width:1100px;height:460px;border:0;transform:scale(0.182);transform-origin:top left"></iframe>
</div>
