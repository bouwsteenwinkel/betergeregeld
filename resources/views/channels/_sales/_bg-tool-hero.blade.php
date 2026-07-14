{{-- Herbruikbare homepage-stijl hero met de voorbeeld-tool-CTA: een bedrijfsnaam-veld
     dat naar /voorbeeld-maken gaat (voorgevuld via ?bedrijf=), net als de homepage.
     Params: $heroTitle (verplicht), $heroSub, $heroEyebrow, $heroNote (optioneel).
     Verwacht $site in scope. --}}
@php
    $heroEyebrow = $heroEyebrow ?? null;
    $heroSub     = $heroSub ?? null;
    $heroNote    = $heroNote ?? 'Gratis. Je zit nergens aan vast.';
    $toolUrl     = $site->url('voorbeeld-maken');
@endphp
<section style="padding: 56px calc(50vw - 50%) 40px; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; font-family: 'Archivo', system-ui, sans-serif;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        @if ($heroEyebrow)
            <div style="font-size: 14px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #1D5DA0; margin-bottom: 14px;">{{ $heroEyebrow }}</div>
        @endif
        <h1 style="font-size: clamp(30px, 4.4vw, 46px); line-height: 1.08; letter-spacing: -0.01em; font-weight: 800; margin: 0 0 14px; max-width: 22ch; color: #1A1A1A;">{{ $heroTitle }}</h1>
        @if ($heroSub)
            <p style="font-size: 19px; line-height: 1.45; color: #4A4844; max-width: 48ch; margin: 0 0 24px;">{{ $heroSub }}</p>
        @endif
        <div style="max-width: 560px;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input id="bg-th-input" placeholder="Typ je bedrijfsnaam…" style="flex: 1 1 240px; height: 60px; padding: 0 18px; font-size: 17px; font-weight: 500; background: #fff; border: 1.5px solid #D8D5D0; border-radius: 6px; outline: none; color: #1A1A1A;">
                <button id="bg-th-btn" type="button" style="height: 60px; padding: 0 30px; background: #12386B; color: #fff; border: none; border-radius: 6px; font-size: 17px; font-weight: 700; cursor: pointer; white-space: nowrap;">Bekijk mijn voorbeeld &rarr;</button>
            </div>
            <p style="font-size: 13px; color: #8A8681; margin: 12px 2px 0; font-weight: 500;">{{ $heroNote }}</p>
        </div>
    </div>
</section>
<script>
(function () {
    var u = @json($toolUrl);
    var i = document.getElementById('bg-th-input'), b = document.getElementById('bg-th-btn');
    function go() { var n = (i && i.value || '').trim(); window.location.href = n ? u + (u.indexOf('?') < 0 ? '?' : '&') + 'bedrijf=' + encodeURIComponent(n) : u; }
    if (b) {
        b.addEventListener('click', go);
        b.addEventListener('mouseenter', function () { b.style.background = '#0C2A50'; });
        b.addEventListener('mouseleave', function () { b.style.background = '#12386B'; });
    }
    if (i) i.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); go(); } });
})();
</script>
