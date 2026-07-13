{{-- Genereert (op preview-sites) het branche-hero-beeld asynchroon en fade't het
     in het full-width hero-blok zodra het klaar is. Faalt het, dan blijft de
     kleur-hero staan. --}}
<script>
(function () {
    var hero = document.querySelector('.hero-full[data-hero-pending]');
    if (!hero) { return; }

    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.getAttribute('content') : '';

    fetch(@json($site->url('hero-image')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(function (r) { return r.json(); }).then(function (d) {
        if (!d || !d.ok || !d.url) { return; }
        var img = new Image();
        img.onload = function () {
            hero.style.setProperty('--hero-img', "url('" + d.url + "')");
            hero.classList.add('has-img');
            hero.removeAttribute('data-hero-pending');
        };
        img.src = d.url;
    }).catch(function () { /* stil: kleur-hero blijft staan */ });
})();
</script>
