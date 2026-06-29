@php /** @var \App\Models\Channel\Block $block */ @endphp
{{-- Funnel-blok: de conversational "gratis voorbeeld"-wizard (verplicht/locked). --}}
@include('channels.partials.lead-wizard', ['site' => $site, 'facet' => $facet ?? ''])
