{{-- Soepel scrollen naar on-page secties bij menu-/CTA-kliks (preview one-pager).
     Geen herladen (preventDefault) en landen met een offset gelijk aan de ECHTE
     sticky-nav-hoogte, zodat je precies op de sectie uitkomt (niet erboven/onder). --}}
<script>
(function () {
    var nav = document.querySelector('.nav');
    function navOffset() { return (nav ? nav.getBoundingClientRect().height : 0) + 14; }

    function scrollToId(id) {
        var el = document.getElementById(id);
        if (!el) { return false; }
        var y = el.getBoundingClientRect().top + window.pageYOffset - navOffset();
        window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
        return true;
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href^="#"]');
        if (!a) { return; }
        var id = (a.getAttribute('href') || '').slice(1);
        if (!id || !document.getElementById(id)) { return; }
        e.preventDefault();
        scrollToId(id);
        if (history.replaceState) { history.replaceState(null, '', '#' + id); }
        // Mobiel menu sluiten als het open staat.
        document.querySelectorAll('.nav.open, .nav-drawer.open, .nav-open').forEach(function (n) { n.classList.remove('open', 'nav-open'); });
        var t = document.querySelector('.nav-toggle[aria-expanded="true"]');
        if (t) { t.setAttribute('aria-expanded', 'false'); }
    });

    // Directe link met #hash: na load netjes op de sectie landen (met offset).
    if (window.location.hash.length > 1) {
        var hid = window.location.hash.slice(1);
        window.addEventListener('load', function () { setTimeout(function () { scrollToId(hid); }, 60); });
    }
})();
</script>
