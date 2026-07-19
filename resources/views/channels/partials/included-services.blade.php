{{--
  "Dit zit er allemaal in" — geruststellingsblok met de standaard meegeleverde
  ondersteunende diensten (hosting, e-mail, SSL, back-ups, onderhoud, SEO,
  domein, koppelingen, performance, meertaligheid). Puur inbegrepen, geen
  prijzen. Data uit config/included_services.php. Full-bleed sectie in de
  channel-designtokens; werkt op elke channel-home.
--}}
@php($__inc = config('included_services'))
@if (! empty($__inc['items']))
  <section aria-labelledby="inbegrepen-heading" style="padding: 72px calc(50vw - 50%); margin: 0 calc(50% - 50vw); width: 100vw; background: #FAF9F7; border-top: 1px solid #E5E3DF;">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
      <h2 id="inbegrepen-heading" style="font-size: clamp(30px, 4.4vw, 48px); line-height: 1.05; letter-spacing: -0.02em; font-weight: 900; margin: 0 0 12px; max-width: 20ch; color: #1A1A1A;">{{ $__inc['heading'] }}</h2>
      @if (! empty($__inc['intro']))
        <p style="font-size: 18px; line-height: 1.5; color: #4A4844; margin: 0 0 40px; max-width: 56ch;">{{ $__inc['intro'] }}</p>
      @endif
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px 40px;">
        @foreach ($__inc['items'] as $__it)
          <div>
            <div style="color: #12386B; margin: 0 0 12px;">@include('channels.partials.included-icon', ['icon' => $__it['icon'] ?? 'default'])</div>
            <h3 style="font-size: 19px; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 6px; color: #1A1A1A;">{{ $__it['title'] }}</h3>
            <p style="font-size: 15.5px; line-height: 1.5; color: #4A4844; margin: 0;">{{ $__it['text'] }}</p>
          </div>
        @endforeach
      </div>
      @if (! empty($__inc['footnote']))
        <p style="display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; color: #12386B; margin: 40px 0 0;">
          <span aria-hidden="true" style="display: inline-flex; width: 22px; height: 22px; border-radius: 50%; background: #12386B; color: #fff; align-items: center; justify-content: center; font-size: 13px;">&check;</span>
          {{ $__inc['footnote'] }}
        </p>
      @endif
    </div>
  </section>
@endif
