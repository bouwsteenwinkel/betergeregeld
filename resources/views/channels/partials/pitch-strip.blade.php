@php
    /** @var \App\Support\ChannelSite $site */
    // Verkoop-strip voor ÓNZE niche-website aan de ondernemer. Niet op de
    // klant-demo (/voorbeeld), waar de bezoeker de site van de klant zelf ziet.
    $items = $site->isDemoContext() ? [] : $site->pitchStripItems();
@endphp
@if ($items)
<div class="pitch-strip">
    <div class="wrap pitch-strip-inner">
        @foreach ($items as $it)
            <span class="pitch-item"><span class="pitch-emoji">{!! $it['icon'] !!}</span>{{ $it['text'] }}</span>
        @endforeach
    </div>
</div>
@endif
