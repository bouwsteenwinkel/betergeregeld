@php
    /** @var \App\Models\Channel\Block $block */
    $items  = (array) $block->c('items', []);
    $detail = $site->image('detail');
    $detSet = $site->imageSrcset('detail');
@endphp
<section data-block="steps" style="background:var(--c-surface)">
    <div class="wrap">
        @if ($detail)
            {{-- Met sfeer-foto: tweekoloms — beeld links, genummerde stappen rechts. --}}
            <div class="grid cols-2" style="align-items:center;gap:2.5rem">
                <div>
                    <img src="{{ $detail }}" @if ($detSet) srcset="{{ $detSet }}" sizes="(max-width:760px) 92vw, 46vw" @endif
                         alt="{{ $site->name() }}" loading="lazy" decoding="async"
                         style="width:100%;height:auto;border-radius:var(--radius);display:block;box-shadow:0 24px 60px -24px rgba(0,0,0,.4)">
                </div>
                <div>
                    @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
                    <div style="display:grid;gap:1.3rem;margin-top:1.2rem">
                        @foreach ($items as $i => $s)
                            <div style="display:flex;gap:1rem;align-items:flex-start">
                                <div style="flex:none;width:38px;height:38px;border-radius:50%;background:var(--c-primary);color:#fff;display:grid;place-items:center;font-weight:800">{{ $i + 1 }}</div>
                                <div><h3>{{ $s['title'] ?? '' }}</h3><p class="muted">{{ $s['text'] ?? '' }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- Zonder foto: de klassieke driekoloms-stappen. --}}
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            <div class="grid cols-3" style="margin-top:1.6rem">
                @foreach ($items as $i => $s)
                    <div>
                        <div style="width:42px;height:42px;border-radius:50%;background:var(--c-primary);color:#fff;display:grid;place-items:center;font-weight:800;margin-bottom:.7rem">{{ $i + 1 }}</div>
                        <h3>{{ $s['title'] ?? '' }}</h3>
                        <p class="muted">{{ $s['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
