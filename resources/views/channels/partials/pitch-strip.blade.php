@php
    /** @var \App\Support\ChannelSite $site */
    // Verkoop-strip voor ÓNZE niche-website aan de ondernemer. Niet op de
    // klant-demo (/voorbeeld), waar de bezoeker de site van de klant zelf ziet.
    $items = $site->isDemoContext() ? [] : $site->pitchStripItems();

    // Line-art iconen (stroke = currentColor), in de stijl van de rest van de site.
    $icons = [
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'globe'  => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 0 20 15.3 15.3 0 0 1 0-20"/>',
        'mobile' => '<rect x="5" y="2" width="14" height="20" rx="2.5"/><path d="M12 18h.01"/>',
        'rocket' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09Z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2Z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
        'check'  => '<path d="M20 6 9 17l-5-5"/>',
    ];
@endphp
@if ($items)
<div class="pitch-strip" data-pitch-rotate>
    <div class="wrap pitch-strip-inner">
        @foreach ($items as $idx => $it)
            <div class="pitch-item{{ $idx === 0 ? ' is-active' : '' }}">
                @if (isset($icons[$it['icon']]))
                    <span class="pitch-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$it['icon']] !!}</svg></span>
                @elseif ($it['icon'] !== '' && \Illuminate\Support\Str::startsWith($it['icon'], '<'))
                    <span class="pitch-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $it['icon'] !!}</svg></span>
                @elseif ($it['icon'] !== '')
                    <span class="pitch-ic pitch-ic-emoji">{!! $it['icon'] !!}</span>
                @endif
                <span class="pitch-txt">
                    <strong class="pitch-title">{{ $it['title'] }}</strong>
                    @if (! empty($it['sub']))<span class="pitch-sub">{{ $it['sub'] }}</span>@endif
                </span>
            </div>
        @endforeach
    </div>
</div>
<script>
(function () {
    var strip = document.querySelector('[data-pitch-rotate]');
    if (!strip) return;
    var items = strip.querySelectorAll('.pitch-item');
    if (items.length < 2) return;
    var i = 0;
    var mq = window.matchMedia('(max-width:680px)');
    // Alleen op mobiel doorrouleren; op desktop staan alle items naast elkaar.
    setInterval(function () {
        if (!mq.matches) return;
        items[i].classList.remove('is-active');
        i = (i + 1) % items.length;
        items[i].classList.add('is-active');
    }, 3400);
    if (mq.addEventListener) {
        mq.addEventListener('change', function () {
            items.forEach(function (el, n) { el.classList.toggle('is-active', n === i); });
        });
    }
})();
</script>
@endif
