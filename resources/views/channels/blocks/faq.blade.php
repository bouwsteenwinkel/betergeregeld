@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
<section data-block="faq">
    <div class="wrap" style="max-width:760px">
        @if ($block->c('heading'))<h2 style="text-align:center;margin-bottom:1.6rem">{{ $block->c('heading') }}</h2>@endif
        <div style="display:grid;gap:.7rem">
            @foreach ($items as $f)
                <details class="card" style="padding:1.1rem 1.3rem">
                    <summary style="font-weight:700;cursor:pointer;list-style:none">{{ $f['q'] ?? '' }}</summary>
                    <p class="muted" style="margin-top:.6rem">{{ $f['a'] ?? '' }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
