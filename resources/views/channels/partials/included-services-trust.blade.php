@php /** @var \App\Support\ChannelSite $site */ @endphp
{{-- "Dit zit er allemaal in" — geruststellingsblok in het class-based theme-
     systeem van de branche-salespagina's (sales-trust). Pakt automatisch de
     themakleur van het channel via var(--c-accent). Data uit
     config/included_services.php. Puur inbegrepen, geen prijzen. --}}
@php($__inc = config('included_services'))
@if (! empty($__inc['items']))
<section data-section="inbegrepen">
    <div class="wrap">
        <span class="kicker"><span class="kicker-line"></span> Inbegrepen</span>
        <h2>{{ $__inc['heading'] }}</h2>
        @if (! empty($__inc['intro']))
            <p style="max-width:58ch">{{ $__inc['intro'] }}</p>
        @endif
        <div class="grid cols-4 feature-grid" style="margin-top:1.4rem">
            @foreach ($__inc['items'] as $__it)
                <div class="feature-card">
                    <div style="color:var(--c-accent);margin-bottom:.5rem">@include('channels.partials.included-icon', ['icon' => $__it['icon'] ?? 'default'])</div>
                    <h3>{{ $__it['title'] }}</h3>
                    <span class="feature-rule"></span>
                    <p>{{ $__it['text'] }}</p>
                </div>
            @endforeach
        </div>
        @if (! empty($__inc['footnote']))
            <p style="margin-top:1.3rem;font-weight:700;color:var(--c-accent)">&check; {{ $__inc['footnote'] }}</p>
        @endif
    </div>
</section>
@endif
