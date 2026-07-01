@php /** @var \App\Support\ChannelSite $site */ @endphp
@extends('channels.layout')

@section('title', $site->homeTitle())
@section('description', $site->homeDescription())

@section('content')
    {{-- Facet-afhankelijke blokken: bij een fase-keuze in de groeiselector worden
         deze live (AJAX) opnieuw gerenderd. De funnel-wizard staat er bewust BUITEN
         (zijn init-script zou na een innerHTML-swap niet opnieuw draaien). --}}
    <div id="facet-zone" data-facet="{{ $facet ?? 'website' }}">
        @include('channels.partials.blocks-fragment')
    </div>

    {{-- Funnel-wizard (en eventuele blokken erna): blijven staan bij een fase-switch. --}}
    @foreach ($site->blocks() as $block)
        @continue(! $block->enabled || $block->type !== 'wizard')
        @include($site->blockView($block->type, $block->block_key), [
            'site'   => $site,
            'block'  => $block,
            'facet'  => $facet ?? 'website',
            'facets' => $facets ?? [],
        ])
    @endforeach

    <script>
    (function () {
        var zone = document.getElementById('facet-zone');
        if (!zone) return;

        // Klik op een fase in de groeiselector → het facet-blok live herrenderen.
        document.addEventListener('click', function (e) {
            var a = e.target.closest('[data-facet-link]');
            if (!a || !zone.contains(a)) return;
            e.preventDefault();
            var url = a.getAttribute('href');
            // Schone fase-URL's (/webshop): de fase staat op de link zelf.
            var facet = a.getAttribute('data-facet') || (url.replace(/[?#].*$/, '').replace(/\/$/, '').split('/').pop()) || 'website';
            zone.style.transition = 'opacity .15s'; zone.style.opacity = '.45';

            fetch(url.replace(/\/$/, '') + '/fragment', { headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' })
                .then(function (r) { if (!r.ok) throw new Error('http'); return r.text(); })
                .then(function (html) {
                    zone.innerHTML = html;
                    zone.setAttribute('data-facet', facet);
                    zone.style.opacity = '';
                    // De wizard staat buiten de zone → z'n facet-veld blijft bestaan; bijwerken.
                    document.querySelectorAll('input[name="facet"]').forEach(function (i) { i.value = facet; });
                    try { history.pushState({ facet: facet }, '', url); } catch (err) {}
                    zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(function () { window.location.href = url; }); // fallback: gewone navigatie
        });

        window.addEventListener('popstate', function () { location.reload(); });
    })();
    </script>
@endsection
