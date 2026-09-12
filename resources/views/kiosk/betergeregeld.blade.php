<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Betergeregeld — live</title>
<style>
  * { box-sizing: border-box; }
  body { margin:0; background:#eef1f6; color:#111; font:13px/1.4 system-ui,-apple-system,'Segoe UI',sans-serif; padding:10px; overflow:hidden; }
  .k-head { display:flex; align-items:baseline; justify-content:space-between; margin-bottom:8px; }
  .k-head h1 { margin:0; font-size:16px; }
  .k-head .sub { color:#6b7280; font-size:11px; }
  .k-live { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#5c6270; font-weight:700; }
  .k-dot { width:8px; height:8px; border-radius:50%; background:#0a8a43; animation:kp 2s infinite; }
  @keyframes kp { 0%{box-shadow:0 0 0 0 rgba(10,138,67,.5);} 70%{box-shadow:0 0 0 7px rgba(10,138,67,0);} 100%{box-shadow:0 0 0 0 rgba(10,138,67,0);} }
  .k-kpis { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; }
  .k-kpi { background:#fff; border:1px solid #e5e7eb; border-radius:11px; padding:8px 11px; box-shadow:0 1px 2px rgba(20,37,76,.04); }
  .k-kpi .lbl { font-size:9px; text-transform:uppercase; letter-spacing:.04em; color:#8a91a0; font-weight:700; }
  .k-kpi .val { font-size:22px; font-weight:800; color:#111; line-height:1.05; margin-top:2px; }
  .k-kpi .sub { font-size:9.5px; color:#6b7280; margin-top:2px; }
  .k-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px; }
  .k-card { background:#fff; border:1px solid #e5e7eb; border-radius:11px; padding:9px 11px; box-shadow:0 1px 2px rgba(20,37,76,.04); }
  .k-h { font-size:11px; font-weight:800; color:#111; margin:0 0 5px; display:flex; justify-content:space-between; }
  .k-h .cnt { color:#8a91a0; font-weight:700; }
  .k-row { display:flex; justify-content:space-between; gap:8px; padding:2px 0; border-top:1px solid #f4f5f7; font-size:11px; }
  .k-row:first-of-type { border-top:0; }
  .k-row .l { color:#111; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .k-row .l small { color:#9aa4b2; font-weight:500; }
  .k-row .r { color:#6b7280; white-space:nowrap; flex:0 0 auto; }
  .k-empty { color:#9aa4b2; font-size:11px; padding:5px 0; }
  .pill { display:inline-block; padding:0 6px; border-radius:8px; font-size:9px; font-weight:700; background:#eef0f2; color:#4a5568; }
  .pill.pos { background:#e4f6ec; color:#0a8a43; } .pill.neg { background:#fce8e8; color:#c02626; }
  .pill.of  { background:#eef3ff; color:#2f6df6; }
</style>
</head>
<body>
@php
  function bg_num($v){ return number_format((float)$v, 0, ',', '.'); }
  function bg_sent($s){ return $s==='positive'?'pos':($s==='negative'?'neg':''); }
@endphp

<div class="k-head">
  <div>
    <h1>Betergeregeld — live</h1>
    <div class="sub">Bezoekers, offerte-aanvragen, afspraken, contact &amp; AI-telefonie</div>
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
    <div class="lbl">Offerte-aanvragen</div>
    <div class="val" id="k-lead">{{ bg_num($d['lead_open']) }}</div>
    <div class="sub">open · <span id="k-leadweek">{{ bg_num($d['lead_week']) }}</span> nieuw deze week</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">Afspraken (komend)</div>
    <div class="val" id="k-afspraak">{{ bg_num($d['afspraak_komend']) }}</div>
    <div class="sub">geplande afspraken</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">Contactaanvragen</div>
    <div class="val" id="k-contact">{{ bg_num($d['contact_week']) }}</div>
    <div class="sub"><span id="k-contactvandaag">{{ bg_num($d['contact_vandaag']) }}</span> vandaag · deze week</div>
  </div>
  <div class="k-kpi">
    <div class="lbl">AI-telefonie vandaag</div>
    <div class="val" id="k-calls">{{ bg_num($d['calls_vandaag']) }}</div>
    <div class="sub"><span id="k-callsweek">{{ bg_num($d['calls_week']) }}</span> deze week</div>
  </div>
</div>

<div class="k-grid2">
  <div class="k-card">
    <p class="k-h">Laatste offerte-aanvragen <span class="cnt">{{ bg_num($d['lead_open']) }} open</span></p>
    <div id="k-leadlijst">
      @forelse ($d['lead_recent'] as $l)
        <div class="k-row">
          <span class="l">{{ $l['naam'] }}{{ $l['bedrijf'] ? ' · '.$l['bedrijf'] : '' }}<small>{{ $l['branche'] ? '  '.$l['branche'] : '' }}</small></span>
          <span class="r"><span class="pill of">{{ $l['status'] }}</span> {{ $l['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Nog geen offerte-aanvragen.</div>
      @endforelse
    </div>
  </div>

  <div class="k-card">
    <p class="k-h">Komende afspraken <span class="cnt">{{ bg_num($d['afspraak_komend']) }}</span></p>
    <div id="k-afspraaklijst">
      @forelse ($d['afspraak_recent'] as $a)
        <div class="k-row">
          <span class="l">{{ $a['naam'] }}<small>  {{ $a['soort'] }}</small></span>
          <span class="r">{{ $a['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Geen geplande afspraken.</div>
      @endforelse
    </div>
  </div>

  <div class="k-card">
    <p class="k-h">Contactaanvragen <span class="cnt">{{ bg_num($d['contact_week']) }} deze week</span></p>
    <div id="k-contactlijst">
      @forelse ($d['contact_recent'] as $c)
        <div class="k-row">
          <span class="l">{{ $c['naam'] }}{{ $c['bedrijf'] ? ' · '.$c['bedrijf'] : '' }} — {{ $c['onderwerp'] }}</span>
          <span class="r">{{ $c['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Geen (echte) contactaanvragen — spam is eruit gefilterd.</div>
      @endforelse
    </div>
  </div>

  <div class="k-card">
    <p class="k-h">Laatste AI-telefoniecalls <span class="cnt">{{ bg_num($d['calls_week']) }} deze week</span></p>
    <div id="k-calllijst">
      @forelse ($d['calls_recent'] as $c)
        <div class="k-row">
          <span class="l">{{ $c['duur'] }} · {{ $c['beurten'] }} beurten<small>{{ $c['samenvatting'] ? ' · '.$c['samenvatting'] : '' }}</small></span>
          <span class="r">{{ $c['wanneer'] }}</span>
        </div>
      @empty
        <div class="k-empty">Nog geen calls.</div>
      @endforelse
    </div>
  </div>
</div>

<script>
(function () {
  var KEY = @json($key);
  var URL = location.pathname + '?key=' + encodeURIComponent(KEY) + '&json=1';
  var num = function (v) { return Number(v).toLocaleString('nl-NL'); };
  var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };
  var set = function (id, t) { var e = document.getElementById(id); if (e) e.textContent = t; };

  function lijst(id, items, leeg, fn) {
    var box = document.getElementById(id);
    if (!box) return;
    box.innerHTML = (items && items.length) ? items.map(fn).join('') : '<div class="k-empty">' + leeg + '</div>';
  }

  function ververs() {
    fetch(URL, { cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) return;
        set('k-tijd', d.tijd);
        set('k-bezoek', num(d.bezoek_vandaag));
        set('k-pv', num(d.pageviews_vandaag));
        set('k-lead', num(d.lead_open));
        set('k-leadweek', num(d.lead_week));
        set('k-afspraak', num(d.afspraak_komend));
        set('k-contact', num(d.contact_week));
        set('k-contactvandaag', num(d.contact_vandaag));
        set('k-calls', num(d.calls_vandaag));
        set('k-callsweek', num(d.calls_week));

        lijst('k-leadlijst', d.lead_recent, 'Nog geen offerte-aanvragen.', function (l) {
          return '<div class="k-row"><span class="l">' + esc(l.naam) +
            (l.bedrijf ? ' · ' + esc(l.bedrijf) : '') + (l.branche ? '<small>  ' + esc(l.branche) + '</small>' : '') +
            '</span><span class="r"><span class="pill of">' + esc(l.status) + '</span> ' + esc(l.wanneer) + '</span></div>';
        });
        lijst('k-afspraaklijst', d.afspraak_recent, 'Geen geplande afspraken.', function (a) {
          return '<div class="k-row"><span class="l">' + esc(a.naam) + '<small>  ' + esc(a.soort) +
            '</small></span><span class="r">' + esc(a.wanneer) + '</span></div>';
        });
        lijst('k-contactlijst', d.contact_recent, 'Geen (echte) contactaanvragen — spam is eruit gefilterd.', function (c) {
          return '<div class="k-row"><span class="l">' + esc(c.naam) +
            (c.bedrijf ? ' · ' + esc(c.bedrijf) : '') + ' — ' + esc(c.onderwerp) +
            '</span><span class="r">' + esc(c.wanneer) + '</span></div>';
        });
        lijst('k-calllijst', d.calls_recent, 'Nog geen calls.', function (c) {
          return '<div class="k-row"><span class="l">' + esc(c.duur) + ' · ' + num(c.beurten) + ' beurten' +
            (c.samenvatting ? '<small> · ' + esc(c.samenvatting) + '</small>' : '') +
            '</span><span class="r">' + esc(c.wanneer) + '</span></div>';
        });
      })
      .catch(function () {});
  }
  setInterval(ververs, 30000);
})();
</script>
</body>
</html>
