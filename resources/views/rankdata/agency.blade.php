@php $fmt = fn ($n) => number_format((int) $n, 0, ',', '.'); @endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $agency?->name ?? 'Rankdata' }} — klanten</title>
    <style>
        :root { --brand: {{ $brand }}; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f5f6f8; color: #1f2430; }
        a { text-decoration: none; }
        .topbar { background: var(--brand); color: #fff; padding: 18px 28px; }
        .topbar .brand { font-weight: 800; letter-spacing: .3px; opacity: .92; font-size: 14px; text-transform: uppercase; }
        .topbar h1 { margin: 2px 0 0; font-size: 22px; }
        .wrap { max-width: 1040px; margin: 0 auto; padding: 24px 20px 60px; }
        .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
        .card { background: #fff; border: 1px solid #e7e9ee; border-radius: 14px; padding: 18px; box-shadow: 0 1px 2px rgba(16,24,40,.04); display: flex; flex-direction: column; }
        .card h2 { margin: 0; font-size: 17px; }
        .card .domain { color: #6b7280; font-size: 13px; margin-top: 2px; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px; margin: 16px 0; }
        .stat .label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
        .stat .value { font-size: 20px; font-weight: 800; }
        .pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .pill.green { background: #dcfce7; color: #166534; } .pill.red { background: #fee2e2; color: #991b1b; } .pill.gray { background: #eef0f4; color: #475569; }
        .btn { margin-top: auto; display: inline-block; text-align: center; background: var(--brand); color: #fff; padding: 10px 14px; border-radius: 10px; font-weight: 700; font-size: 14px; }
        .muted { color: #9aa1ad; } .foot { margin-top: 36px; font-size: 12px; color: #9aa1ad; text-align: center; }
        .head-row { display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">{{ $agency?->name ?? 'Rankdata' }}</div>
        <h1>Klantenoverzicht</h1>
    </div>
    <div class="wrap">
        <div class="head-row">
            <div class="muted">{{ $clients->count() }} {{ $clients->count() === 1 ? 'klant' : 'klanten' }}{{ $isSuper ? ' (alle bureaus)' : '' }} · statistieken laatste 30 dagen</div>
        </div>

        @if ($clients->isEmpty())
            <div class="card">Nog geen klanten gekoppeld.</div>
        @else
            <div class="grid">
                @foreach ($clients as $c)
                    <div class="card">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
                            <div>
                                <h2>{{ $c['name'] }}</h2>
                                <div class="domain">{{ $c['domain'] ?? '—' }}</div>
                            </div>
                            <span class="pill {{ $c['uptime'] === null ? 'gray' : ($c['current'] === 'up' ? 'green' : 'red') }}">
                                {{ $c['uptime'] !== null ? number_format($c['uptime'], 1, ',', '.') . '%' : 'n.v.t.' }}
                            </span>
                        </div>
                        @if ($isSuper && $c['agencyName'])
                            <div class="muted" style="font-size:12px;margin-top:4px">Bureau: {{ $c['agencyName'] }}</div>
                        @endif
                        <div class="stats">
                            <div class="stat"><div class="label">Clicks 30d</div><div class="value">{{ $fmt($c['clicks']) }}</div></div>
                            <div class="stat"><div class="label">Impressies</div><div class="value">{{ $fmt($c['impr']) }}</div></div>
                            <div class="stat"><div class="label">Gem. positie</div><div class="value">{{ $c['pos'] !== null ? number_format($c['pos'], 1, ',', '.') : '—' }}</div></div>
                            <div class="stat"><div class="label">Uptime 7d</div><div class="value">{{ $c['uptime'] !== null ? number_format($c['uptime'], 1, ',', '.') . '%' : '—' }}</div></div>
                        </div>
                        <a class="btn" href="{{ route('rankdata.client', ['locale' => app()->getLocale(), 'tenant' => $c['tenant']->id]) }}">Bekijk dashboard →</a>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="foot">{{ $agency?->name ?? 'Rankdata' }} · powered by Beter Geregeld</div>
    </div>
</body>
</html>
