@php /** @var \App\Models\Channel\Block $block */ @endphp
{{-- Groeidiamant-groeiselector — hergebruikt de bestaande partial. --}}
@include('channels.partials.groeipad', [
    'site'      => $site,
    'facet'     => $facet ?? 'website',
    'facets'    => $facets ?? (array) config('groeidiamant.facets', []),
    'facetBase' => $facetBase ?? '',
])
