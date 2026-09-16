<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>{{ $b['titel'] }}</title>
<style>
	/* dompdf-vriendelijke CSS in de huisstijl van Betergeregeld (resources/css/app.css):
	   ink #0A0F1F, accent #14B8A6, oppervlak #FAFAFA. Geen flex/grid: dompdf kent ze niet. */
	@page { margin: 14mm 16mm 16mm; }
	body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #0A0F1F; line-height: 1.42; margin: 0; }
	p { margin: 0 0 2.5mm; }
	h1 { font-size: 18pt; line-height: 1.15; margin: 0 0 2.5mm; letter-spacing: -0.02em; }
	h2 { font-size: 8.4pt; text-transform: uppercase; letter-spacing: 1.6px; color: #0D9488; margin: 5mm 0 2mm; }
	.muted { color: rgba(10,15,31,.62); }
	.small { font-size: 8.4pt; }

	/* kop met logo en nummer */
	.top { width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
	.top td { vertical-align: middle; padding: 0; }
	.logo-b { display: inline-block; width: 9mm; height: 9mm; border-radius: 50%; background: #12386B; color: #fff; text-align: center; font-weight: bold; font-size: 14pt; line-height: 9mm; margin-right: 2.5mm; }
	.merk { font-weight: bold; font-size: 11pt; vertical-align: middle; }
	.merk span { color: rgba(10,15,31,.55); font-weight: normal; font-size: 8.4pt; }
	.belkaart { background: #0A0F1F; color: #fff; border-radius: 3mm; padding: 3mm 5mm; text-align: right; white-space: nowrap; }
	.belkaart .lbl { font-size: 7.5pt; text-transform: uppercase; letter-spacing: 1.4px; color: rgba(255,255,255,.7); }
	.belkaart .nr { font-size: 16pt; font-weight: bold; letter-spacing: .5px; line-height: 1.2; white-space: nowrap; }

	.kicker { font-size: 8.4pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.8px; color: #14B8A6; margin-bottom: 2mm; }
	.intro { font-size: 9.4pt; color: rgba(10,15,31,.78); margin-bottom: 1mm; }

	table.feiten { width: 100%; border-collapse: collapse; }
	table.feiten td { padding: 1.3mm 2.5mm 1.3mm 0; vertical-align: top; border-bottom: 1px solid rgba(10,15,31,.08); }
	table.feiten td.k { width: 30mm; font-weight: bold; color: rgba(10,15,31,.7); }

	table.data { width: 100%; border-collapse: collapse; font-size: 8.4pt; }
	table.data th { text-align: left; font-size: 7.6pt; text-transform: uppercase; letter-spacing: 1px; color: rgba(10,15,31,.55); padding: 0 2.5mm 1.5mm 0; border-bottom: 1.5px solid #0A0F1F; }
	table.data td { padding: 1.5mm 2.5mm 1.5mm 0; vertical-align: top; border-bottom: 1px solid rgba(10,15,31,.10); }
	table.data td.mono { font-weight: bold; white-space: nowrap; }

	table.probeer { width: 100%; border-collapse: collapse; font-size: 8.6pt; }
	table.probeer td { padding: 1.5mm 2.5mm 1.5mm 0; vertical-align: top; border-bottom: 1px solid rgba(10,15,31,.08); }
	table.probeer td.v { width: 66mm; font-weight: bold; }
	table.probeer td.v .bol { color: #14B8A6; font-weight: bold; margin-right: 1.5mm; }
	table.probeer td.a { color: rgba(10,15,31,.78); }

	.twee { width: 100%; border-collapse: collapse; margin-top: 2mm; }
	.twee td { width: 50%; vertical-align: top; padding: 0; }
	.twee td.l { padding-right: 4mm; }
	.twee td.r { padding-left: 4mm; }
	.box { background: #F2F4F7; border-radius: 2.5mm; padding: 2.5mm 4mm; }
	.box.niet { border-left: 3pt solid #B03A2E; }
	.box.tip { border-left: 3pt solid #14B8A6; }
	.box h2 { margin-top: 0; }
	.box.niet h2 { color: #B03A2E; }
	ul { margin: 0; padding-left: 4.5mm; }
	li { margin: 0 0 1mm; }

	.slot { margin-top: 4mm; background: #0A0F1F; color: #fff; border-radius: 3mm; padding: 3.5mm 5mm; }
	.slot p { margin: 0; color: rgba(255,255,255,.88); font-size: 9pt; }
	.slot b { color: #14B8A6; }
	.voet { margin-top: 3.5mm; font-size: 7.8pt; color: rgba(10,15,31,.5); border-top: 1px solid rgba(10,15,31,.10); padding-top: 2.5mm; }
</style>
</head>
<body>

<table class="top">
	<tr>
		<td>
			<span class="logo-b">B</span><span class="merk">Beter Geregeld ICT <span>· AI-telefonie</span></span>
		</td>
		<td style="width:58mm">
			<div class="belkaart">
				<div class="lbl">Bel de demo</div>
				<div class="nr">{{ $b['nummer'] }}</div>
			</div>
		</td>
	</tr>
</table>

<div class="kicker">Infoblad bij de demo</div>
<h1>{{ $b['titel'] }}</h1>
<p class="intro">{{ $b['intro'] }}</p>

<h2>Goed om te weten</h2>
<table class="feiten">
	@foreach ($b['feiten'] as $f)
		<tr><td class="k">{{ $f[0] }}</td><td>{{ $f[1] }}</td></tr>
	@endforeach
</table>

<h2>{{ $b['data_titel'] }}</h2>
<table class="data">
	<tr>@foreach ($b['data_kop'] as $k)<th>{{ $k }}</th>@endforeach</tr>
	@foreach ($b['data'] as $r)
		<tr>@foreach ($r as $i => $cel)<td class="{{ $i === 0 ? 'mono' : '' }}">{{ $cel }}</td>@endforeach</tr>
	@endforeach
</table>

<h2>Probeer dit, en dit hoor je dan</h2>
<table class="probeer">
	@foreach ($b['probeer'] as $i => $p)
		<tr><td class="v"><span class="bol">{{ $i + 1 }}</span>{{ $p[0] }}</td><td class="a">{{ $p[1] }}</td></tr>
	@endforeach
</table>

<table class="twee">
	<tr>
		<td class="l"><div class="box niet"><h2>Wat hij bewust níét doet</h2><ul>@foreach ($b['niet'] as $n)<li>{{ $n }}</li>@endforeach</ul></div></td>
		<td class="r"><div class="box tip"><h2>Tips voor een goed gesprek</h2><ul>@foreach ($b['tips'] as $t)<li>{{ $t }}</li>@endforeach</ul></div></td>
	</tr>
</table>

<div class="slot"><p><b>En bij jou?</b> {{ $b['slot'] }}</p></div>

<div class="voet">Er wordt niet opgenomen; er wordt een uitgeschreven samenvatting gemaakt. {{ $b['bedrijf'] }} en de genoemde personen zijn verzonnen. Beter Geregeld ICT · betergeregeld.com · {{ $site->domain() ?? '' }} · stand {{ $datum }}</div>

</body>
</html>
