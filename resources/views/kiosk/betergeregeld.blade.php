<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Betergeregeld — live</title>
<style>
  * { box-sizing: border-box; }
  body { margin:0; background:#eef1f6; color:#111; font:13px/1.4 system-ui,-apple-system,'Segoe UI',sans-serif; padding:12px; overflow:hidden; }
  .k-head { display:flex; align-items:baseline; justify-content:space-between; margin-bottom:10px; }
  .k-head h1 { margin:0; font-size:17px; }
  .k-head .sub { color:#6b7280; font-size:11px; }
  .k-live { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#5c6270; font-weight:700; }
  .k-dot { width:8px; height:8px; border-radius:50%; background:#0a8a43; animation:kp 2s infinite; }
  @keyframes kp { 0%{box-shadow:0 0 0 0 rgba(10,138,67,.5);} 70%{box-shadow:0 0 0 7px rgba(10,138,67,0);} 100%{box-shadow:0 0 0 0 rgba(10,138,67,0);} }
  .k-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
  .k-kpi { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:10px 12px; box-shadow:0 1px 2px rgba(20,37,76,.04); }
  .k-kpi .lbl { font-size:9.5px; text-transform:uppercase; letter-spacing:.04em; color:#8a91a0; font-weight:700; }
  .k-kpi .val { font-size:24px; font-weight:800; color:#111; line-height:1.05; margin-top:3px; }
  .k-kpi .sub { font-size:10px; color:#6b7280; margin-top:2px; }
  .k-cols { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px; }
  .k-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:10px 12px; box-shadow:0 1px 2px rgba(20,37,76,.04); }
  .k-h { font-size:11px; font-weight:800; color:#111; margin:0 0 6px; display:flex; justify-content:space-between; }
  .k-h .cnt { color:#8a91a0; font-weight:700; }
  .k-row { display:flex; justify-content:space-between; gap:8px; padding:3px 0; border-top:1px solid #f4f5f7; font-size:11.5px; }
  .k-row:first-of-type { border-top:0; }
  .k-row .l { color:#111; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .k-row .l small { color:#9aa4b2; font-weight:500; }
  .k-row .r { color:#6b7280; white-space:nowrap; flex:0 0 auto; }
  .k-empty { color:#9aa4b2; font-size:11.5px; padding:6px 0; }
  .k-tbl { width:100%; border-collapse:collapse; font-size:11px; }
  .k-tbl th { text-align:left; font-size:9px; text-transform:uppercase; letter-spacing:.03em; color:#8a91a0; font-weight:700; padding:2px 5px; border-bottom:1px solid #eef0f2; }
  .k-tbl td { padding:2px 5px; border-bottom:1px solid #f4f5f7; }
  .k-tbl td.r, .k-tbl th.r { text-align:right; }
  .pill { display:inline-block; padding:0 6px; border-radius:8px; font-size:9.5px; font-weight:700; }
  .pill.pos { background:#e4f6ec; color:#0a8a43; } .pill.neg { background:#fce8e8; color:#c02626; } .pill.neu { background:#eef0f2; color:#6b7280; }
</style>
</head>
<body>
@php
  function bg_num($v){ return number_format((float)$v, 0, ',', '.'); }
  function bg_eur($v){ return '€ ' . number_format((float)$v, 2, ',', '.'); }
  function bg_sent($s){ return $s==='positive'?'pos':($s==='negative'?'neg':'neu'); }
@endphp

<div class="k-head">
  <div>
    <h1>Betergeregeld — live</h1>
    <div class="sub">Bezoekers, contactaanvragen en AI-telefonie</div>
  </div>
  <div class="k-live"><span class="k-dot"></span> Live · bijgewerkt <span id="k-tijd">{{ $d['tijd'] }}</span></div>
</div>

<div class="k-kpis">
  <div class="k-kpi">
    <div class="lbl">Bezoekers vandaag</div>
    <div class="val" id="k-bezoek">{{ bg_num($d['bezoek_vandaag']) }}</div>
    <div class="sub"><span id="k-pv">{{ bg_num($d['pageviews_vandaag']) }}</span> paginaweergaven</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">Contactaanvragen vandaag</div>
    <div class="val" id="k-contact">{{ bg_num($d['contact_vandaag']) }}</div>
    <div class="sub"><span id="k-contactweek">{{ bg_num($d['contact_week']) }}</span> deze week</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">AI-telefonie vandaag</div>
    <div class="val" id="k-calls">{{ bg_num($d['calls_vandaag']) }}</div>
    <div class="sub"><span id="k-callsweek">{{ bg_num($d['calls_week']) }}</span> deze week</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">Gem. gespreksduur</div>
    <div class="val" id="k-duur">{{ intdiv($d['calls_gem_duur'],60) }}:{{ str_pad($d['calls_gem_duur']%60,2,'0',STR_PAD_LEFT) }}</div>
    <div class="sub">van de calls van vandaag</div>
  </div>
</div>

<div class="k-cols">
  <div class="k-card">
    <p class="k-h">Laatste contactaanvragen <span class="cnt">{{ bg_num($d['contact_week']) }} deze week</span></p>
    <div id="k-contactlijst">
      @forelse ($d['contact_recent'] as $c)
        <div class="k-row">
          <span class="l">{{ $c['naam'] }}@if($c['bedrijf'])<small> · {{ $c['bedrijf'] }}</small>@endif — {{ $c['onderwerp'] }}</span>
          <span class="r">{{ $c['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Nog geen aanvragen.</div>
      @endforelse
    </div>
  </div>

  <div class="k-card">
    <p class="k-h">Laatste AI-telefoniecalls <span class="cnt">{{ bg_num($d['calls_week']) }} deze week</span></p>
    <div id="k-calllijst">
      @forelse ($d['calls_recent'] as $c)
        <div class="k-row">
          <span class="l">
            @if($c['sentiment'])<span class="pill {{ bg_sent($c['sentiment']) }}">{{ $c['sentiment'] }}</span> @endif
            {{ $c['duur'] }} · {{ $c['beurten'] }} beurten<small>{{ $c['samenvatting'] ? ' · '.$c['samenvatting'] : '' }}</small>
          </span>
          <span class="r">{{ $c['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Nog geen calls.</div>
      @endforelse
    </div>
  </div>
</div>

<div class="k-card" style="margin-top:8px;">
  <p class="k-h">Bezoekers per site — vandaag</p>
  <div id="k-sitebox">
    @if (count($d['bezoek_per_site']))
      <table class="k-tbl">
        <thead><tr><th>Site</th><th class="r">Bezoekers</th><th class="r">Paginaweergaven</th></tr></thead>
        <tbody>
          @foreach ($d['bezoek_per_site'] as $s)
            <tr><td>{{ $s['site'] }}</td><td class="r">{{ bg_num($s['bezoekers']) }}</td><td class="r">{{ bg_num($s['pv']) }}</td></tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="k-empty">Nog geen bezoekers vandaag.</div>
    @endif
  </div>
</div>

<script>
(function () {
  var KEY = @json($key);
  var URL = location.pathname + '?key=' + encodeURIComponent(KEY) + '&json=1';
  var num = function (v) { return Number(v).toLocaleString('nl-NL'); };
  var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };
  var set = function (id, t) { var e = document.getElementById(id); if (e) e.textContent = t; };
  function sentClass(s){ return s==='positive'?'pos':(s==='negative'?'neg':'neu'); }

  function ververs() {
    fetch(URL, { cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) return;
        set('k-tijd', d.tijd);
        set('k-bezoek', num(d.bezoek_vandaag));
        set('k-pv', num(d.pageviews_vandaag));
        set('k-contact', num(d.contact_vandaag));
        set('k-contactweek', num(d.contact_week));
        set('k-calls', num(d.calls_vandaag));
        set('k-callsweek', num(d.calls_week));
        var mm = Math.floor(d.calls_gem_duur / 60), ss = d.calls_gem_duur % 60;
        set('k-duur', mm + ':' + (ss < 10 ? '0' : '') + ss);

        var cl = document.getElementById('k-contactlijst');
        if (cl) {
          cl.innerHTML = (d.contact_recent && d.contact_recent.length)
            ? d.contact_recent.map(function (c) {
                return '<div class="k-row"><span class="l">' + esc(c.naam) +
                  (c.bedrijf ? '<small> · ' + esc(c.bedrijf) + '</small>' : '') + ' — ' + esc(c.onderwerp) +
                  '</span><span class="r">' + esc(c.wanneer) + '</span></div>';
              }).join('')
            : '<div class="k-empty">Nog geen aanvragen.</div>';
        }

        var ll = document.getElementById('k-calllijst');
        if (ll) {
          ll.innerHTML = (d.calls_recent && d.calls_recent.length)
            ? d.calls_recent.map(function (c) {
                return '<div class="k-row"><span class="l">' +
                  (c.sentiment ? '<span class="pill ' + sentClass(c.sentiment) + '">' + esc(c.sentiment) + '</span> ' : '') +
                  esc(c.duur) + ' · ' + num(c.beurten) + ' beurten' +
                  (c.samenvatting ? '<small> · ' + esc(c.samenvatting) + '</small>' : '') +
                  '</span><span class="r">' + esc(c.wanneer) + '</span></div>';
              }).join('')
            : '<div class="k-empty">Nog geen calls.</div>';
        }

        var sb = document.getElementById('k-sitebox');
        if (sb) {
          if (d.bezoek_per_site && d.bezoek_per_site.length) {
            var h = '<table class="k-tbl"><thead><tr><th>Site</th><th class="r">Bezoekers</th><th class="r">Paginaweergaven</th></tr></thead><tbody>';
            d.bezoek_per_site.forEach(function (s) {
              h += '<tr><td>' + esc(s.site) + '</td><td class="r">' + num(s.bezoekers) + '</td><td class="r">' + num(s.pv) + '</td></tr>';
            });
            sb.innerHTML = h + '</tbody></table>';
          } else {
            sb.innerHTML = '<div class="k-empty">Nog geen bezoekers vandaag.</div>';
          }
        }
      })
      .catch(function () {});
  }
  setInterval(ververs, 30000);
})();
</script>
</body>
</html>
