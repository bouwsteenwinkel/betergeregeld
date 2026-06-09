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
        .siteswitch { background: #fff; border-bottom: 1px solid #e7e9ee; }
        .siteswitch-inner { max-width: 1040px; margin: 0 auto; padding: 10px 20px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .sw-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-right: 4px; }
        .sw-pill { padding: 6px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #374151; background: #f1f2f5; border: 1px solid transparent; }
        .sw-pill.active { background: var(--brand); color: #fff; }
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
            <div class="domain">
                @if ($selected)
                    {{ $selected->label }} · {{ $domain }}@if(($selected->type ?? 'website') === 'app') · applicatie @endif · laatste 30 dagen
                @else
                    nog geen sites gekoppeld
                @endif
            </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;font-size:13px">
            @if ($canPickClient)
                <span style="opacity:.85">Bureau-/beheerweergave</span>
            @endif
            <span style="opacity:.9">Ingelogd als <strong>{{ ($authUser ?? auth()->user())?->email }}</strong></span>
            <div style="display:flex;gap:8px;align-items:center">
                <form method="POST" action="{{ route('rankdata.report', ['locale' => app()->getLocale(), 'tenant' => $tenant->id]) }}" style="margin:0">
                    @csrf
                    <button type="submit" style="background:#fff;color:var(--brand);border:none;border-radius:8px;padding:5px 12px;font-size:13px;font-weight:700;cursor:pointer">Rapport e-mailen</button>
                </form>
                <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}" style="margin:0">
                    @csrf
                    <button type="submit" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:8px;padding:5px 12px;font-size:13px;font-weight:600;cursor:pointer">Uitloggen</button>
                </form>
            </div>
            @if ($tenant->report_sent_at)
                <span style="opacity:.7;font-size:11px">Rapport laatst verzonden {{ \Illuminate\Support\Carbon::parse($tenant->report_sent_at)->format('d-m-Y H:i') }}</span>
            @endif
        </div>
    </div>

    @if ($sites->count() > 1)
        <div class="siteswitch">
            <div class="siteswitch-inner">
                <span class="sw-label">Sites:</span>
                @foreach ($sites as $s)
                    <a class="sw-pill {{ $selected && $s->id === $selected->id ? 'active' : '' }}"
                       href="{{ route('rankdata.client', ['locale' => app()->getLocale(), 'tenant' => $tenant->id]) }}?site={{ $s->id }}">
                        {{ $s->label }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="wrap">
        @if (session('rapport_message'))
            <div class="card" style="margin-top:20px;border-left:4px solid #16a34a">{{ session('rapport_message') }}</div>
        @endif

        {{-- Aanbevolen acties (AI, op basis van openstaande issues) --}}
        @if ($advice ?? null)
            <div class="card" style="margin-top:20px;border-left:4px solid var(--brand)">
                <div class="section-title" style="margin:0 0 8px">Aanbevolen acties</div>
                <div style="font-size:14px;line-height:1.5">{!! \Illuminate\Support\Str::markdown($advice) !!}</div>
            </div>
        @endif

        @if (! $selected)
            <div class="card" style="margin-top:20px">Deze klant heeft nog geen sites. Voeg een website of applicatie toe in het bureau-panel.</div>
        @elseif ($seo)
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

        {{-- Paginakwaliteit (AI-scan) --}}
        @if ($quality)
            <div class="section-title">Paginakwaliteit</div>
            <div class="card">
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <span class="gauge"><span class="score" style="color:{{ $perfColor($quality['score']) }}">{{ $quality['score'] }}</span><span class="muted">/ 100</span></span>
                    @if (! is_null($quality['trend']))
                        <span class="{{ $quality['trend'] >= 0 ? 'up' : 'down' }}">{{ $quality['trend'] >= 0 ? '▲' : '▼' }} {{ abs($quality['trend']) }}</span>
                    @endif
                    <span style="display:flex;gap:6px">
                        @if ($quality['fail'])<span class="pill red">{{ $quality['fail'] }} kritiek</span>@endif
                        @if ($quality['warn'])<span class="pill amber">{{ $quality['warn'] }} aandachtspunt{{ $quality['warn'] === 1 ? '' : 'en' }}</span>@endif
                        <span class="pill green">{{ $quality['pass'] }} ok</span>
                    </span>
                    <span class="muted" style="font-size:12px;margin-left:auto">gescand {{ \Illuminate\Support\Carbon::parse($quality['scanned_at'])->format('d-m-Y') }}</span>
                </div>
                <div class="bar" style="margin-top:10px"><span style="width:{{ $quality['score'] }}%;background:{{ $perfColor($quality['score']) }}"></span></div>
                @if (count($quality['top']))
                    <div style="margin-top:14px;display:flex;flex-direction:column;gap:6px">
                        @foreach ($quality['top'] as $f)
                            <div style="font-size:13px;display:flex;gap:8px;align-items:flex-start">
                                <span class="pill {{ $f['status'] === 'fail' ? 'red' : 'amber' }}" style="flex:none">{{ $f['status'] === 'fail' ? 'Kritiek' : 'Let op' }}</span>
                                <span>{{ $f['finding'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Beveiliging --}}
        @if ($security ?? null)
            @php $catLabels = ['safe_browsing' => 'Malware', 'blacklist' => 'Blacklist', 'mixed_content' => 'Mixed content', 'broken_link' => 'Broken link']; @endphp
            <div class="section-title">Beveiliging</div>
            <div class="card">
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <span class="pill {{ $security['safe_browsing'] === 'ok' ? 'green' : ($security['safe_browsing'] === 'flagged' ? 'red' : 'amber') }}">
                        Malware: {{ $security['safe_browsing'] === 'ok' ? 'schoon' : ($security['safe_browsing'] === 'flagged' ? 'GEMARKEERD' : 'onbekend') }}
                    </span>
                    <span class="pill {{ $security['blacklisted'] ? 'red' : 'green' }}">{{ $security['blacklisted'] ? 'Op blacklist' : 'Geen blacklist' }}</span>
                    <span class="pill {{ $security['mixed'] > 0 ? 'amber' : 'green' }}">{{ $security['mixed'] }} mixed content</span>
                    <span class="pill {{ $security['broken'] > 0 ? 'amber' : 'green' }}">{{ $security['broken'] }} broken link{{ $security['broken'] === 1 ? '' : 's' }}</span>
                    <span class="muted" style="font-size:12px;margin-left:auto">gescand {{ \Illuminate\Support\Carbon::parse($security['scanned_at'])->format('d-m-Y') }} · {{ $security['links_checked'] }} links</span>
                </div>
                @if (count($security['findings']))
                    <div style="margin-top:14px;display:flex;flex-direction:column;gap:6px">
                        @foreach ($security['findings'] as $f)
                            <div style="font-size:13px;display:flex;gap:8px;align-items:flex-start">
                                <span class="pill {{ $f['severity'] === 'hoog' ? 'red' : ($f['severity'] === 'middel' ? 'amber' : 'green') }}" style="flex:none">{{ $catLabels[$f['category']] ?? $f['category'] }}</span>
                                <span>{{ $f['finding'] }}@if ($f['url']) — <span class="muted">{{ \Illuminate\Support\Str::limit($f['url'], 70) }}</span>@endif</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="muted" style="font-size:13px;margin-top:10px">Geen beveiligingsproblemen gevonden. ✅</div>
                @endif
            </div>
        @endif

        {{-- Software & updates --}}
        @if ($software ?? null)
            <div class="section-title">Software &amp; updates</div>
            <div class="card">
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <span class="muted" style="font-size:13px">{{ $software['components'] }} componenten</span>
                    <span class="pill {{ $software['updates'] > 0 ? 'amber' : 'green' }}">{{ $software['updates'] }} update{{ $software['updates'] === 1 ? '' : 's' }} beschikbaar</span>
                    <span class="pill {{ $software['vuln_count'] > 0 ? 'red' : 'green' }}">{{ $software['vuln_count'] }} kwetsbaarhe{{ $software['vuln_count'] === 1 ? 'id' : 'den' }}</span>
                    @if ($software['integrity_checked'])
                        <span class="pill {{ $software['integrity_count'] > 0 ? 'red' : 'green' }}">{{ $software['integrity_count'] }} gewijzigde core-bestand{{ $software['integrity_count'] === 1 ? '' : 'en' }}</span>
                    @endif
                    <span class="muted" style="font-size:12px;margin-left:auto">bijgewerkt {{ \Illuminate\Support\Carbon::parse($software['reported_at'])->format('d-m-Y') }}</span>
                </div>
                @if (count($software['vulns']))
                    <div style="margin-top:14px;display:flex;flex-direction:column;gap:6px">
                        @foreach ($software['vulns'] as $v)
                            <div style="font-size:13px;display:flex;gap:8px;align-items:flex-start">
                                <span class="pill {{ in_array($v['severity'], ['critical','high']) ? 'red' : ($v['severity'] === 'medium' ? 'amber' : 'green') }}" style="flex:none">{{ ucfirst($v['severity'] ?? 'onbekend') }}</span>
                                <span>
                                    <strong>{{ $v['name'] }}</strong>@if ($v['version']) {{ $v['version'] }}@endif — {{ $v['title'] }}
                                    @if ($v['patched_in'])<span class="muted"> (opgelost in {{ $v['patched_in'] }})</span>@endif
                                    @if ($v['cve'])<span class="muted"> · {{ $v['cve'] }}</span>@endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="muted" style="font-size:13px;margin-top:10px">Geen bekende kwetsbaarheden in de gerapporteerde software. ✅</div>
                @endif
                @if ($software['integrity_checked'] && count($software['integrity_issues']))
                    <div style="margin-top:14px">
                        <div class="muted" style="font-size:12px;margin-bottom:4px">Afwijkende core-bestanden (t.o.v. officiële WP-checksums):</div>
                        <div style="display:flex;flex-direction:column;gap:4px">
                            @foreach ($software['integrity_issues'] as $i)
                                <div style="font-size:13px;display:flex;gap:8px;align-items:flex-start">
                                    <span class="pill {{ $i['type'] === 'missing' ? 'amber' : 'red' }}" style="flex:none">{{ $i['type'] === 'missing' ? 'Ontbreekt' : ($i['type'] === 'unexpected' ? 'Onverwacht' : 'Gewijzigd') }}</span>
                                    <span class="font-mono" style="font-family:monospace">{{ $i['path'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Abonnement / facturatie --}}
        @if (($canManageBilling ?? false) && ($monthlyCost['plan'] ?? null))
            <div class="section-title">Abonnement</div>
            <div class="card">
                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <strong style="font-size:18px">€ {{ number_format($monthlyCost['total'], 2, ',', '.') }}<span class="muted" style="font-size:13px;font-weight:400"> / maand</span></strong>
                    <span class="muted" style="font-size:12px">{{ $monthlyCost['plan'] }} · {{ $monthlyCost['sites'] }} site{{ $monthlyCost['sites'] === 1 ? '' : 's' }}@if ($monthlyCost['discount_percent'] > 0) · {{ rtrim(rtrim(number_format($monthlyCost['discount_percent'], 2, ',', '.'), '0'), ',') }}% korting @endif</span>
                    @if ($subscription)
                        <span class="pill green" style="margin-left:auto">Actief t/m {{ \Illuminate\Support\Carbon::parse($subscription->current_period_ends_at)->format('d-m-Y') }}</span>
                    @else
                        <span class="pill amber" style="margin-left:auto">Nog niet geactiveerd</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('rankdata.checkout', ['locale' => 'nl', 'tenant' => $tenant->id]) }}" style="margin-top:12px">
                    @csrf
                    <button type="submit" style="background:var(--brand);color:#fff;border:none;border-radius:10px;padding:10px 18px;font-weight:600;font-size:14px;cursor:pointer">
                        {{ $subscription ? 'Verleng / betaal nu' : 'Abonnement activeren & betalen' }}
                    </button>
                </form>
            </div>
        @endif

        <div class="foot">Statistieken via {{ $agency?->name ?? 'Rankdata' }} · powered by Beter Geregeld</div>
    </div>
</body>
</html>
