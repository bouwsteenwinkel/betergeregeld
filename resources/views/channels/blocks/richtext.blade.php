@php /** @var \App\Models\Channel\Block $block */ @endphp
<section data-block="richtext">
    <div class="wrap prose" style="max-width:720px">
        @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
        {{-- 'html' is bewust rauwe HTML (door de admin/designer ingevoerd). --}}
        {!! $block->c('html', '') !!}
    </div>
</section>
