{{-- Gedeelde betergeregeld-header voor het bedrijfswebsite-kanaal.
     Gebruikt op de homepage (_sales/bedrijfswebsite) EN, via een key-guard in
     channels/layout.blade.php, op alle overige pagina's van dit kanaal, zodat de
     hele funnel dezelfde header heeft. Links wijzen naar echte routes (niet naar
     homepage-ankers) zodat de nav overal werkt. Verwacht $site in scope. --}}
<style>
    .bgn-links { display: flex; align-items: center; gap: 28px; }
    .bgn-links a:hover { color: #12386B; }
    .bgn-phone:hover { color: #0C2A50; }
    .bgn-cta-btn:hover { background: #0C2A50; }
    /* Hamburger: alleen op mobiel zichtbaar. */
    .bgn-burger { display: none; }
    .bgn-drawer { display: none; }
    @media (max-width: 759px) {
        /* De inline display:flex is verwijderd van .bgn-links, dus deze regel wint nu. */
        .bgn-links, .bgn-cta-btn { display: none; }
        .bgn-burger { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; padding: 0; background: transparent; border: 1.5px solid #E5E3DF; border-radius: 6px; color: #12386B; cursor: pointer; }
        .bgn-drawer.is-open { display: block; }
        .bgn-drawer a { display: block; padding: 14px 24px; font-size: 16px; font-weight: 600; color: #1A1A1A; text-decoration: none; border-top: 1px solid #E5E3DF; }
        .bgn-drawer a.bgn-drawer-cta { color: #fff; background: #12386B; font-weight: 700; }
    }
</style>
<nav style="position: sticky; top: 0; z-index: 60; margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; border-bottom: 1px solid #E5E3DF; font-family: 'Archivo', system-ui, sans-serif;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ $site->url('') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <div style="width: 30px; height: 30px; background: #12386B; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 17px;">B</div>
            <span style="font-weight: 800; font-size: 18px; letter-spacing: -0.02em; color: #1A1A1A;">betergeregeld</span>
        </a>
        <div style="display: flex; align-items: center; gap: 28px;">
            {{-- Links naar secties óp de homepage (de losse pagina's zijn verwijderd).
                 display:flex staat nu in de <style> zodat de mobiele media-query hem kan verbergen. --}}
            <div class="bgn-links">
                <a href="{{ $site->url('') . '#werkwijze' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Werkwijze</a>
                <a href="{{ $site->url('') . '#prijzen' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Prijzen</a>
                <a href="{{ $site->url('') . '#contact' }}" style="font-size: 15px; font-weight: 600; color: #6B6864; text-decoration: none;">Contact</a>
            </div>
            <a href="tel:+31882545101" class="bgn-phone" style="font-size: 18px; font-weight: 800; letter-spacing: -0.01em; color: #12386B; text-decoration: none;">088 2545101</a>
            <a href="{{ $site->url('voorbeeld-maken') }}" class="bgn-cta-btn" style="background: #12386B; color: #fff; padding: 11px 20px; border-radius: 6px; font-size: 15px; font-weight: 700; text-decoration: none; white-space: nowrap;">Bekijk mijn voorbeeld</a>
            {{-- Mobiele hamburger: opent de drawer hieronder. --}}
            <button type="button" class="bgn-burger" aria-label="Menu" aria-expanded="false" onclick="(function(b){var d=document.getElementById('bgn-drawer');var o=d.classList.toggle('is-open');b.setAttribute('aria-expanded',o);})(this)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>
    {{-- Mobiele uitklap-drawer (alleen zichtbaar < 760px via .bgn-drawer regels). --}}
    <div id="bgn-drawer" class="bgn-drawer">
        <a href="{{ $site->url('') . '#werkwijze' }}">Werkwijze</a>
        <a href="{{ $site->url('') . '#prijzen' }}">Prijzen</a>
        <a href="{{ $site->url('') . '#contact' }}">Contact</a>
        <a href="{{ $site->url('voorbeeld-maken') }}" class="bgn-drawer-cta">Bekijk mijn voorbeeld</a>
    </div>
</nav>
