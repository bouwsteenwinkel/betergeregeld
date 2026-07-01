@php
    /**
     * Illustratief afspraak-planner-mockup dat rechts in de hero zweeft. Toont
     * "een stukje van de oplossing" (online boeken). Content uit config
     * channels.<key>.hero_widget, met defaults.
     */
    $w        = (array) $site->get('hero_widget', []);
    $title    = $w['title'] ?? 'Maak een afspraak';
    $days     = $w['days'] ?? ['Ma', 'Di 14', 'Wo', 'Do', 'Vr'];
    $onDay    = $w['on_day'] ?? 1;
    $slots    = $w['slots'] ?? ['09:00', '10:30', '13:00', '15:30'];
    $selSlot  = $w['sel_slot'] ?? 1;
    $services = $w['services'] ?? [['Afspraak', '30 min']];
    $selSvc   = $w['sel_service'] ?? 0;
@endphp
<div class="hero-mock" aria-hidden="true">
    <div class="hm-head"><span class="dot"></span> Online afspraak</div>
    <div class="hm-title">{{ $title }}</div>
    <div class="hm-days">
        @foreach ($days as $i => $d)<span class="{{ $i === $onDay ? 'on' : '' }}">{{ $d }}</span>@endforeach
    </div>
    <div class="hm-slots">
        @foreach ($slots as $i => $s)<span class="{{ $i === $selSlot ? 'sel' : '' }}">{{ $s }}</span>@endforeach
    </div>
    <div class="hm-svc">
        @foreach ($services as $i => $svc)<div class="{{ $i === $selSvc ? 'sel' : '' }}"><span>{{ $svc[0] ?? '' }}</span><span>{{ $svc[1] ?? '' }}</span></div>@endforeach
    </div>
    <button class="hm-btn" type="button">Bevestig afspraak</button>
</div>
