@php /** @var \App\Support\ChannelSite $site */ $f = $facet ?? 'website'; @endphp
{{-- Facet-afhankelijke blokken (alles behalve de funnel-wizard). Wordt zowel door
     home-blocks ge-include als los gerenderd door ChannelSiteController@homeFragment
     voor de live (AJAX) fase-switch. --}}
@foreach ($site->blocksForFacet($f) as $block)
    @continue(! $block->enabled || $block->type === 'wizard')
    @php $block->setActiveFacet($f); @endphp
    @include($site->blockView($block->type, $block->block_key), [
        'site'      => $site,
        'block'     => $block,
        'facet'     => $f,
        'facets'    => $facets ?? (array) config('groeidiamant.facets', []),
        'facetBase' => $facetBase ?? '',
    ])
@endforeach
