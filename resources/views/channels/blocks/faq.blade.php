@php /** @var \App\Models\Channel\Block $block */ $items = (array) $block->c('items', []); @endphp
@if ($items)
	@push('head')
		{{-- FAQPage-schema voor rich results. \x40 = @ (Blade-directive-escape). --}}
		<script type="application/ld+json">
		{!! json_encode([
			"\x40context"   => 'https://schema.org',
			"\x40type"      => 'FAQPage',
			'mainEntity' => array_values(array_map(fn ($f) => [
				"\x40type"       => 'Question',
				'name'           => (string) ($f['q'] ?? ''),
				'acceptedAnswer' => ["\x40type" => 'Answer', 'text' => (string) ($f['a'] ?? '')],
			], array_filter($items, fn ($f) => ! empty($f['q']) && ! empty($f['a'])))),
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
		</script>
	@endpush
@endif
<section data-block="faq">
    <div class="wrap">
        <div style="text-align:center;max-width:640px;margin:0 auto 2rem">
            @if ($block->c('eyebrow'))<span class="kicker" style="justify-content:center"><span class="kicker-line"></span> {{ $block->c('eyebrow') }}</span>@endif
            @if ($block->c('heading'))<h2>{{ $block->c('heading') }}</h2>@endif
            @if ($block->c('sub'))<p class="muted" style="margin-top:.5rem">{{ $block->c('sub') }}</p>@endif
        </div>
        @include('channels.partials.faq-accordion', ['items' => $items, 'sharp' => (bool) $block->c('punchy')])
    </div>
    @if ($block->c('punchy'))
        <style>
            /* Neutrale, nette blokken: wit / licht grijs, GEEN accentkleur. Strakke
               hoeken. Overschrijft de accent-stijl van het globale .faq-blok. */
            .faq-sharp details{border-radius:6px;border:1px solid color-mix(in srgb,var(--c-ink) 12%,transparent);background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)}
            .faq-sharp details[open]{border-color:color-mix(in srgb,var(--c-ink) 20%,transparent);background:color-mix(in srgb,var(--c-ink) 4%,#fff);box-shadow:0 1px 2px rgba(0,0,0,.04)}
            .faq-sharp details[open] summary{color:var(--c-ink)}
            .faq-sharp summary::after{background:color-mix(in srgb,var(--c-ink) 8%,transparent);color:var(--c-muted);display:flex;align-items:center;justify-content:center;line-height:1;font-size:1.25rem}
            .faq-sharp details[open] summary::after{background:color-mix(in srgb,var(--c-ink) 14%,transparent);color:var(--c-ink)}
        </style>
    @endif
</section>
