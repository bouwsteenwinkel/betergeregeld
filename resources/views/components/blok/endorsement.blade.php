{{-- Bouwblok: Groeidiamant-keurmerk. Discreet onderaan een niche-site:
     het moederbedrijf endorsed de specialist. Tekst/url uit config/channel_sites
     defaults of per kanaal overschreven. --}}
@props([
    'text' => null,
    'url'  => null,
])
@php
    $text = $text ?: config('channel_sites.defaults.endorsement', 'Volgens de Groeidiamant van Betergeregeld ICT');
    $url  = $url  ?: config('channel_sites.defaults.endorsement_url', 'https://betergeregeld.nl');
@endphp
<div class="text-center py-5 text-xs" style="color:var(--muted,#9a9a93)">
    <span aria-hidden="true">◆</span>
    <a href="{{ $url }}" target="_blank" rel="noopener" class="hover:underline" style="color:inherit">{{ $text }}</a>
</div>
