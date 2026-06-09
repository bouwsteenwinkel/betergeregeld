<div class="space-y-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs dark:border-white/10 dark:bg-white/5">
        <span class="font-mono font-semibold">{{ $advisory->package }}</span>
        @if ($advisory->severity) · ernst: {{ ucfirst($advisory->severity) }} @endif
        @if ($advisory->cve) · {{ $advisory->cve }} @endif
        @if ($advisory->patched_in) · opgelost in {{ $advisory->patched_in }} @endif
    </div>

    <div class="prose prose-sm max-w-none dark:prose-invert">
        {!! \Illuminate\Support\Str::markdown($explanation) !!}
    </div>

    @if ($advisory->link)
        <p class="text-xs">
            <a href="{{ $advisory->link }}" target="_blank" rel="noopener" class="text-primary-600 underline dark:text-primary-400">
                Bron / technische details openen
            </a>
        </p>
    @endif

    <p class="text-xs text-gray-400">Uitleg automatisch gegenereerd; controleer bij twijfel de bron.</p>
</div>
