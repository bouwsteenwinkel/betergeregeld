@php
    /** @var \App\Models\Channel\Block $block */
    $hours = (array) $block->c('hours', []);
    $address = $block->c('address') ?? $site->brand('address');
    $phone   = $block->c('phone') ?? $site->brand('phone');
    $email   = $block->c('email') ?? $site->brand('email');
@endphp
<section data-block="location" style="background:var(--c-surface)">
    <div class="wrap" style="display:grid;gap:2rem;grid-template-columns:1fr;@media(min-width:760px){grid-template-columns:1fr 1fr}">
        <div>
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            <div class="muted" style="margin-top:1rem;display:grid;gap:.5rem">
                @if ($address)<div>📍 {{ $address }}</div>@endif
                @if ($phone)<div>📞 <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></div>@endif
                @if ($email)<div>✉ <a href="mailto:{{ $email }}">{{ $email }}</a></div>@endif
            </div>
        </div>
        @if ($hours)
            <div>
                <h3 style="margin-bottom:.6rem">Openingstijden</h3>
                <div style="display:grid;gap:.3rem">
                    @foreach ($hours as $h)
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid color-mix(in srgb,var(--c-ink) 8%,transparent);padding:.4rem 0">
                            <span>{{ is_array($h) ? ($h['day'] ?? $h[0] ?? '') : '' }}</span>
                            <span class="muted">{{ is_array($h) ? ($h['time'] ?? $h[1] ?? '') : $h }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
