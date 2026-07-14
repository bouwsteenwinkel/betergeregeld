<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Mijn voorbeelden · Beter Geregeld</title>
    <style>
        :root{--ink:#0f172a;--muted:#64748b;--accent:#1685c4;--line:#e2e8f0;--bg:#f7f9fb}
        *{box-sizing:border-box}
        body{margin:0;font:16px/1.55 system-ui,-apple-system,'Segoe UI',sans-serif;color:var(--ink);background:var(--bg)}
        .wrap{max-width:820px;margin:0 auto;padding:2.4rem 1.2rem 4rem}
        header h1{font-size:1.7rem;margin:0 0 .3rem}
        header p{color:var(--muted);margin:0 0 2rem}
        .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:1.2rem 1.3rem;margin-bottom:1.1rem;box-shadow:0 18px 40px -32px rgba(15,23,42,.4)}
        .card.fav{border-color:var(--accent);box-shadow:0 0 0 2px color-mix(in srgb,var(--accent) 22%,transparent)}
        .card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}
        .card h2{font-size:1.15rem;margin:0}
        .fav-tag{display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:700;color:var(--accent);background:color-mix(in srgb,var(--accent) 10%,#fff);padding:.25rem .6rem;border-radius:999px}
        .tags{display:flex;flex-wrap:wrap;gap:.4rem;margin:.7rem 0 1rem}
        .tag{font-size:.8rem;color:var(--muted);background:var(--bg);border:1px solid var(--line);border-radius:8px;padding:.2rem .55rem}
        .tag b{color:var(--ink);font-weight:600}
        .actions{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center}
        .btn{display:inline-flex;align-items:center;gap:.4rem;background:var(--accent);color:#fff;text-decoration:none;font-weight:700;font-size:.92rem;padding:.6rem 1.1rem;border-radius:8px;border:0;cursor:pointer}
        .btn.sec{background:#fff;color:var(--ink);border:1px solid var(--line);font-weight:600}
        .empty{color:var(--muted)}
        .cta{margin-top:1.8rem;background:var(--ink);color:#fff;border-radius:14px;padding:1.4rem 1.5rem}
        .cta h3{margin:0 0 .3rem}
        .cta a{color:#fff}
        footer{margin-top:2rem;color:var(--muted);font-size:.85rem;text-align:center}
        footer a{color:var(--muted)}
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <h1>Hoi {{ $lead->contact_name ?: 'daar' }}, hier staan je voorbeelden</h1>
            <p>Bewaar deze pagina, hij is van jou. Kies je favoriet of laat ons er eentje uitwerken.</p>
        </header>

        @forelse ($previews as $p)
            @php $d = $p->designSummary(); @endphp
            <div class="card {{ $p->favorite ? 'fav' : '' }}">
                <div class="card-top">
                    <h2>{{ $d['bedrijf'] ?? ($lead->company ?: 'Jouw voorbeeld') }}</h2>
                    @if ($p->favorite)
                        <span class="fav-tag">
                            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 15l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"/></svg>
                            Favoriet
                        </span>
                    @endif
                </div>
                <div class="tags">
                    @foreach ($d as $label => $value)
                        @continue($label === 'bedrijf')
                        <span class="tag"><b>{{ ucfirst($label) }}:</b> {{ $value }}</span>
                    @endforeach
                </div>
                <div class="actions">
                    <a class="btn" href="{{ $p->url() }}" target="_blank" rel="noopener">Bekijk voorbeeld</a>
                    @unless ($p->favorite)
                        <form method="post" action="/mijn-voorbeelden/{{ $token }}/favoriet" style="margin:0">
                            @csrf
                            <input type="hidden" name="saved_id" value="{{ $p->id }}">
                            <button type="submit" class="btn sec">Maak favoriet</button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <p class="empty">Je hebt nog geen voorbeelden opgeslagen.</p>
        @endforelse

        <div class="cta">
            <h3>Zullen we hem afmaken?</h3>
            <p>Antwoord op onze mail of neem contact op, dan werken we je favoriet vrijblijvend uit tot een echte website.</p>
            <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
        </div>

        <footer>
            Beter Geregeld · <a href="/mijn-voorbeelden/{{ $token }}/afmelden">Geen herinneringen meer ontvangen</a>
        </footer>
    </div>
</body>
</html>
