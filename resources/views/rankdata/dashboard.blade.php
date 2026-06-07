@php
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
    $daily = $seo['daily'] ?? [];
    $w = 760; $h = 150; $n = count($daily);
    $maxC = max(1, max(array_map(fn ($d) => $d['clicks'], $daily ?: [['clicks' => 1]])));
    $pts = [];
    foreach ($daily as $i => $d) {
        $x = $n > 1 ? round($i / ($n - 1) * $w, 1) : 0;
        $y = round($h - ($d['clicks'] / $maxC) * ($h - 18) - 4, 1);
        $pts[] = "$x,$y";
    }
    $line = implode(' ', $pts);
    $area = $line ? "0,$h $line $w,$h" : '';
    $perfColor = fn ($s) => $s >= 90 ? '#16a34a' : ($s >= 50 ? '#d97706' : '#dc2626');
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }} — statistieken</title>
    <style>
        :root { --brand: {{ $brand }}; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f5f6f8; color: #1f2430; }
        a { color: var(--brand); }
        .topbar { background: var(--brand); color: #fff; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .topbar .brand { font-weight: 800; letter-spacing: .3px; opacity: .92; font-size: 14px; text-transform: uppercase; }
        .topbar h1 { margin: 2px 0 0; font-size: 22px; }
        .topbar .domain { opacity: .85; font-size: 13px; }
        .wrap { max-width: 1040px; margin: 0 auto; padding: 24px 20px 60px; }
        .grid { display: grid; gap: 16px; }
        .kpis { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 8px; }
        .card { background: #fff; border: 1px solid #e7e9ee; border-radius: 14px; padding: 18px 18px; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
        .kpi .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
        .kpi .value { font-size: 28px; font-weight: 800; margin-top: 6px; }
        .kpi .sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .up { color: #16a34a; } .down { color: #dc2626; } .muted { color: #6b7280; }
        .section-title { font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .5px; margin: 26px 4px 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 9px 8px; border-bottom: 1px solid #eef0f4; }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #6b7280; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        .pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .pill.green { background: #dcfce7; color: #166534; } .pill.amber { background: #fef3c7; color: #92400e; } .pill.red { background: #fee2e2; color: #991b1b; }
        .two { grid-template-columns: 1fr 1fr; } .three { grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 760px) { .two, .three { grid-template-columns: 1fr; } }
        .gauge { display: flex; align-items: baseline; gap: 8px; }
        .gauge .score { font-size: 30px; font-weight: 800; }
        .bar { height: 7px; border-radius: 999px; background: #eef0f4; overflow: hidden; margin-top: 8px; }
        .bar > span { display: block; height: 100%; }
        .foot { margin-top: 36px; font-size: 12px; color: #9aa1ad; text-align: center; }
        .trunc { max-width: 420px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <div class="brand">{{ $agency?->name ?? 'Rankdata' }}</div>
            <h1>{{ $tenant->name }}</h1>
            <div class="domain">{{ $domain ?? '—' }} · statistieken laatste 30 dagen</div>
        </div>
        @if ($canPickClient)
            <div style="font-size:13px;opacity:.9">Bureau-/beheerweergave</div>
        @endif
    </div>

    <div class="wrap">
        @if ($seo)
            {{-- KPI's --}}
            <div class="grid kpis">
                <div class="card kpi">
                    <div class="label">Clicks (30d)</div>
                    <div class="value">{{ $fmt($seo['clicks']) }}</div>
                    <div class="sub">
                        @if (! is_null($seo['trend']))
                            <span class="{{ $seo['trend'] >= 0 ? 'up' : 'down' }}">{{ $seo['trend'] >= 0 ? '▲' : '▼' }} {{ abs($seo['trend']) }}%</span> vs vorige 7d
                        @else <span class="muted">—</span> @endif
                    </div>
                </div>
                <div class="card kpi"><div class="label">Impressies (30d)</div><div class="value">{{ $fmt($seo['impressions']) }}</div><div class="sub muted">vertoningen in Google</div></div>
                <div class="card kpi"><div class="label">CTR</div><div class="value">{{ number_format($seo['ctr'], 1, ',', '.') }}%</div><div class="sub muted">doorklikratio</div></div>
                <div class="card kpi"><div class="label">Gem. positie</div><div class="value">{{ number_format($seo['position'], 1, ',', '.') }}</div><div class="sub muted">in zoekresultaten</div></div>
                @if ($uptime)
                    <div class="card kpi"><div class="label">Uptime (7d)</div><div class="value">{{ $uptime['percent'] !== null ? number_format($uptime['percent'], 2, ',', '.') . '%' : '—' }}</div><div class="sub muted">{{ $uptime['avg_latency'] ? $uptime['avg_latency'] . ' ms gem.' : '' }}</div></div>
                @endif
            </div>

            {{-- Grafiek --}}
            <div class="section-title">Clicks per dag</div>
            <div class="card">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="160" preserveAspectRatio="none">
                    @if ($area)
                        <polygon points="{{ $area }}" fill="var(--brand)" opacity="0.10"></polygon>
                        <polyline points="{{ $line }}" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"></polyline>
                    @endif
                </svg>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#9aa1ad;margin-top:4px">
                    <span>{{ $daily[0]['date'] ?? '' }}</span><span>{{ end($daily)['date'] ?? '' }}</span>
                </div>
            </div>

            {{-- Zoekwoorden + pagina's --}}
            <div class="grid two">
                <div>
                    <div class="section-title">Top zoekwoorden</div>
                    <div class="card">
                        <table>
                            <tr><th>Zoekwoord</th><th class="num">Clicks</th><th class="num">Impr.</th><th class="num">Pos.</th></tr>
                            @foreach ($seo['topQueries'] as $q)
                                <tr><td>{{ $q['query'] }}</td><td class="num">{{ $fmt($q['clicks']) }}</td><td class="num">{{ $fmt($q['impr']) }}</td><td class="num">{{ number_format($q['pos'], 1, ',', '.') }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div>
                    <div class="section-title">Top pagina's</div>
                    <div class="card">
                        <table>
                            <tr><th>Pagina</th><th class="num">Impr.</th><th class="num">Clicks</th></tr>
                            @foreach ($seo['topPages'] as $p)
                                <tr><td class="trunc">{{ \Illuminate\Support\Str::after($p['page'], $domain) ?: '/' }}</td><td class="num">{{ $fmt($p['impr']) }}</td><td class="num">{{ $fmt($p['clicks']) }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card" style="margin-top:20px">Nog geen Search Console-data gekoppeld voor deze klant.</div>
        @endif

        {{-- PageSpeed --}}
        @if ($psi && ($psi['mobile'] || $psi['desktop']))
            <div class="section-title">PageSpeed (Core Web Vitals)</div>
            <div class="grid two">
                @foreach (['mobile' => 'Mobiel', 'desktop' => 'Desktop'] as $key => $label)
                    @if ($psi[$key])
                        @php $p = $psi[$key]; @endphp
                        <div class="card">
                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <strong>{{ $label }}</strong>
                                <span class="muted" style="font-size:12px">LCP {{ number_format($p['lcp'], 1, ',', '.') }}s · CLS {{ number_format($p['cls'], 2, ',', '.') }} · INP {{ $p['inp'] }}ms</span>
                            </div>
                            <div class="gauge" style="margin-top:10px">
                                <span class="score" style="color:{{ $perfColor($p['performance']) }}">{{ $p['performance'] }}</span>
                                <span class="muted">/ 100 prestatie</span>
                            </div>
                            <div class="bar"><span style="width:{{ $p['performance'] }}%;background:{{ $perfColor($p['performance']) }}"></span></div>
                            <div class="muted" style="font-size:12px;margin-top:10px">SEO {{ $p['seo'] }} · Toegankelijkheid {{ $p['accessibility'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Uptime --}}
        @if ($uptime)
            <div class="section-title">Beschikbaarheid (7 dagen)</div>
            <div class="card">
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <span class="pill {{ $uptime['current'] === 'up' ? 'green' : 'red' }}">{{ $uptime['current'] === 'up' ? 'Online' : 'Offline' }}</span>
                    <strong style="font-size:18px">{{ $uptime['percent'] !== null ? number_format($uptime['percent'], 2, ',', '.') . '% uptime' : '—' }}</strong>
                    @if ($uptime['avg_latency'])<span class="muted">· {{ $uptime['avg_latency'] }} ms gemiddelde responstijd</span>@endif
                </div>
                @if (count($uptime['incidents']))
                    <div style="margin-top:12px">
                        <div class="muted" style="font-size:12px;margin-bottom:4px">Storingen:</div>
                        @foreach ($uptime['incidents'] as $inc)
                            <div style="font-size:13px">⚠️ {{ $inc['from'] }} – {{ $inc['to'] }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="muted" style="font-size:13px;margin-top:10px">Geen storingen in deze periode. ✅</div>
                @endif
            </div>
        @endif

        <div class="foot">Statistieken via {{ $agency?->name ?? 'Rankdata' }} · powered by Beter Geregeld</div>
    </div>
</body>
</html>
