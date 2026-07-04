<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Google-agenda koppelen</title>
    <style>
        body{font-family:system-ui,Arial,sans-serif;background:#f4f7fa;color:#1c2530;margin:0;padding:40px 20px}
        .card{max-width:560px;margin:0 auto;background:#fff;border-radius:16px;padding:32px;box-shadow:0 20px 50px -30px rgba(0,0,0,.3)}
        h1{font-size:22px;margin:0 0 6px}
        p{color:#5b6b78;line-height:1.6}
        .status{display:inline-flex;align-items:center;gap:8px;font-weight:700;padding:8px 14px;border-radius:999px;margin:8px 0 18px}
        .on{background:#e6f7ec;color:#137a3a}.off{background:#fdecec;color:#b3261e}
        .btn{display:inline-block;background:#1f4e79;color:#fff;font-weight:700;text-decoration:none;padding:12px 22px;border-radius:8px;border:0;cursor:pointer;font:inherit}
        .btn.ghost{background:none;color:#5b6b78;border:1px solid #d5dbe0}
        .msg{padding:12px 16px;border-radius:10px;margin-bottom:18px;font-weight:600}
        .msg.ok{background:#e6f7ec;color:#137a3a}.msg.err{background:#fdecec;color:#b3261e}
        a.back{color:#1f4e79;font-size:14px;display:inline-block;margin-top:20px}
    </style>
</head>
<body>
    <div class="card">
        <h1>Google-agenda voor de afsprakenplanner</h1>
        @if (session('success'))<div class="msg ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="msg err">{{ session('error') }}</div>@endif

        @unless ($configured)
            <div class="status off">● Niet geconfigureerd</div>
            <p>Er is nog geen <code>GOOGLE_CLIENT_ID</code>/<code>GOOGLE_CLIENT_SECRET</code> ingesteld in de omgeving.</p>
        @elseif ($connected)
            <div class="status on">● Gekoppeld</div>
            <p>De centrale Google-agenda is gekoppeld. Elke geboekte afspraak maakt automatisch een agenda-event met Google Meet-link, en je eigen agenda-afspraken blokkeren de beschikbare tijden.</p>
            <form method="POST" action="{{ route('google-agenda.disconnect') }}">@csrf<button class="btn ghost">Ontkoppelen</button></form>
        @else
            <div class="status off">● Nog niet gekoppeld</div>
            <p>Koppel het Google-account dat de agenda is. Je logt één keer in en geeft toestemming; daarna verloopt alles automatisch.</p>
            <a class="btn" href="{{ route('google-agenda.connect') }}">Koppel Google-agenda</a>
        @endunless

        <a class="back" href="{{ url('/admin') }}">← Terug naar de admin</a>
    </div>
</body>
</html>
