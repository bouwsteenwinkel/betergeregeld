@php
    /** @var \App\Support\ChannelSite $site */
    // $items: array van ['label' => ..., 'url' => ...]. Laatste = huidige pagina (zonder link).
    $items = array_values(array_filter((array) ($items ?? []), fn ($i) => !empty($i['label'])));
@endphp
@if (count($items) > 1)
    @php
        $bcLd = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
        foreach ($items as $i => $it) {
            $el = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['label']];
            if (!empty($it['url'])) { $el['item'] = $it['url']; }
            $bcLd['itemListElement'][] = $el;
        }
    @endphp
    <nav class="breadcrumb" aria-label="Kruimelpad">
        <div class="wrap">
            @foreach ($items as $it)
                @if (!$loop->last && !empty($it['url']))
                    <a href="{{ $it['url'] }}">{{ $it['label'] }}</a><span class="bc-sep" aria-hidden="true">/</span>
                @else
                    <span aria-current="page">{{ $it['label'] }}</span>
                @endif
            @endforeach
        </div>
    </nav>
    @push('head')
        <script type="application/ld+json">{!! json_encode($bcLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
@endif
