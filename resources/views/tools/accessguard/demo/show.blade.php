@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
	$stateColor = [
		'has_access' => ['#ecfdf5', '#065f46', '#a7f3d0', '✓'],
		'no_access' => ['#f8fafc', '#64748b', '#e2e8f0', '—'],
		'needs_review' => ['#fef3c7', '#92400e', '#fde68a', '?'],
		'unknown' => ['#f8fafc', '#94a3b8', '#e2e8f0', '·'],
	];
	$riskColor = [
		5 => '#dc2626',
		4 => '#ea580c',
		3 => '#d97706',
		2 => '#64748b',
		1 => '#94a3b8',
	];
@endphp

@section('title', 'AccessGuard ' . ($isEn ? 'demo' : 'demo') . ' — ' . config('app.name'))
@section('description', $isEn
	? 'Try AccessGuard live — no signup. See a realistic access matrix, review cycle and risk flags.'
	: 'Probeer AccessGuard live — geen account nodig. Bekijk een realistische toegangsmatrix, review-cyclus en risico-flags.')

@section('content')

<style>
.agd {
	--agd-text: #0f172a;
	--agd-muted: rgba(15,23,42,.65);
	--agd-border: rgba(15,23,42,.10);
	--agd-card: #ffffff;
	--agd-bg: #f5f7fb;
	--agd-primary: #ff7a18;
	--agd-primary-hover: #e86a0f;
	color: var(--agd-text);
	font-family: 'Inter', system-ui, -apple-system, sans-serif;
	background: var(--agd-bg);
}
.agd-banner {
	background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
	color: #fff;
	padding: 14px 24px;
	font-size: 14px;
	font-weight: 600;
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 14px;
	flex-wrap: wrap;
	text-align: center;
}
.agd-banner-pill {
	background: rgba(255,255,255,.15);
	padding: 3px 10px;
	border-radius: 999px;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: .5px;
	text-transform: uppercase;
}
.agd-banner a { color: #ff7a18; text-decoration: none; font-weight: 700; }
.agd-banner a:hover { text-decoration: underline; }

.agd-hero { padding: 56px 24px 24px; text-align: center; max-width: 900px; margin: 0 auto; }
.agd-hero h1 {
	font-size: clamp(1.9rem, 3.2vw, 2.7rem);
	font-weight: 900;
	margin: 0 0 12px;
	letter-spacing: -0.02em;
	line-height: 1.08;
}
.agd-hero p { margin: 0; font-size: 1.0625rem; color: var(--agd-muted); line-height: 1.55; font-weight: 500; max-width: 620px; margin: 0 auto; }

.agd-section { max-width: 1160px; margin: 0 auto; padding: 32px 24px; }
.agd-section-title { display: flex; justify-content: space-between; align-items: end; margin-bottom: 16px; }
.agd-section-title h2 { margin: 0; font-size: 1.375rem; font-weight: 800; letter-spacing: -0.02em; }
.agd-section-title p { margin: 4px 0 0; color: var(--agd-muted); font-size: 0.9375rem; }

.agd-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.agd-stat-card {
	background: var(--agd-card);
	border: 1px solid var(--agd-border);
	border-radius: 14px;
	padding: 18px 20px;
	box-shadow: 0 1px 3px rgba(15,23,42,.04);
}
.agd-stat-val { font-size: 1.75rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1; }
.agd-stat-label { font-size: 0.8125rem; color: var(--agd-muted); margin-top: 4px; font-weight: 600; }

.agd-card {
	background: var(--agd-card);
	border: 1px solid var(--agd-border);
	border-radius: 16px;
	padding: 24px;
	box-shadow: 0 1px 3px rgba(15,23,42,.04);
}

.agd-matrix-wrap { overflow-x: auto; }
.agd-matrix {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
	font-size: 13px;
	min-width: 680px;
}
.agd-matrix th, .agd-matrix td {
	padding: 8px 10px;
	border-bottom: 1px solid var(--agd-border);
	text-align: center;
	vertical-align: middle;
}
.agd-matrix th { font-size: 11px; font-weight: 700; color: var(--agd-muted); text-transform: uppercase; letter-spacing: .4px; background: var(--agd-bg); }
.agd-matrix td.agd-person-cell { text-align: left; font-weight: 600; white-space: nowrap; }
.agd-matrix td.agd-person-cell small { display: block; font-weight: 500; color: var(--agd-muted); font-size: 11px; }
.agd-cell {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 34px;
	padding: 4px 8px;
	border-radius: 6px;
	font-weight: 700;
	font-size: 13px;
	border: 1px solid;
}
.agd-person-inactive { color: #94a3b8; }
.agd-person-inactive .agd-person-dot { background: #f87171; }
.agd-person-dot { display: inline-block; width: 8px; height: 8px; border-radius: 999px; background: #22c55e; margin-right: 6px; vertical-align: middle; }

.agd-cycle-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--agd-border); gap: 12px; }
.agd-cycle-item:last-child { border-bottom: none; }
.agd-cycle-meta { font-size: 13px; color: var(--agd-muted); }
.agd-cycle-name { font-weight: 600; font-size: 14px; }
.agd-badge {
	display: inline-flex;
	align-items: center;
	padding: 2px 10px;
	border-radius: 999px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: .4px;
}
.agd-badge-has { background: #ecfdf5; color: #065f46; }
.agd-badge-no { background: #fef2f2; color: #991b1b; }
.agd-badge-review { background: #fef3c7; color: #92400e; }
.agd-badge-unknown { background: #f1f5f9; color: #475569; }

.agd-risk {
	display: flex;
	gap: 14px;
	padding: 14px 0;
	border-bottom: 1px solid var(--agd-border);
	align-items: flex-start;
}
.agd-risk:last-child { border-bottom: none; }
.agd-risk-sev {
	flex-shrink: 0;
	width: 34px; height: 34px;
	border-radius: 8px;
	display: flex; align-items: center; justify-content: center;
	color: #fff; font-weight: 900; font-size: 14px;
}
.agd-risk-body { flex: 1; min-width: 0; }
.agd-risk-title { font-weight: 700; font-size: 14px; margin: 0 0 3px; }
.agd-risk-desc { font-size: 13px; color: var(--agd-muted); margin: 0; }
.agd-risk-kind { display: inline-block; margin-top: 6px; font-size: 11px; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

.agd-cta-final {
	background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
	color: #fff;
	padding: 56px 24px;
	text-align: center;
	border-radius: 20px;
	margin: 16px 0 48px;
}
.agd-cta-final h2 { margin: 0 0 10px; font-size: 1.75rem; font-weight: 900; letter-spacing: -0.02em; }
.agd-cta-final p { margin: 0 auto 24px; color: rgba(255,255,255,.75); font-size: 1rem; max-width: 520px; line-height: 1.55; }
.agd-btn {
	display: inline-flex; align-items: center; gap: 8px;
	padding: 12px 22px;
	border-radius: 10px;
	font-weight: 700;
	font-size: 15px;
	text-decoration: none;
	transition: transform .12s ease, box-shadow .12s ease;
}
.agd-btn-primary { background: var(--agd-primary); color: #fff; box-shadow: 0 10px 24px rgba(255,122,24,.28); }
.agd-btn-primary:hover { background: var(--agd-primary-hover); color: #fff; transform: translateY(-1px); }
.agd-btn-secondary { background: rgba(255,255,255,.10); color: #fff; border: 1px solid rgba(255,255,255,.28); }
.agd-btn-secondary:hover { background: rgba(255,255,255,.18); color: #fff; }
.agd-cta-actions { display: inline-flex; gap: 12px; flex-wrap: wrap; justify-content: center; }

@media (max-width: 720px) {
	.agd-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="agd">

<div class="agd-banner">
	<span class="agd-banner-pill">{{ $isEn ? 'Live demo' : 'Live demo' }}</span>
	<span>{{ $isEn
		? 'Realistic SMB data — explore freely. Resets every 24 h.'
		: 'Realistische MKB-data — verken vrij. Reset elke 24 uur.' }}</span>
	<a href="{{ route('register', ['locale' => $locale]) }}">{{ $isEn ? 'Start free in your own space →' : 'Start gratis in je eigen omgeving →' }}</a>
</div>

<div class="agd-hero">
	<h1>{{ $isEn ? 'See AccessGuard at work.' : 'Zie AccessGuard aan het werk.' }}</h1>
	<p>{{ $isEn
		? 'A fictional 6-person company, 6 systems, one live review cycle, two real risk flags. Exactly what your first login looks like.'
		: 'Een fictief bedrijf van 6 mensen, 6 systemen, één lopende review-cyclus, twee echte risico-flags. Zo ziet jouw eerste login er ook uit.' }}</p>
</div>

<div class="agd-section">
	<div class="agd-stats">
		<div class="agd-stat-card">
			<div class="agd-stat-val">{{ $counts['people'] }}</div>
			<div class="agd-stat-label">{{ $isEn ? 'People' : 'Personen' }}</div>
		</div>
		<div class="agd-stat-card">
			<div class="agd-stat-val">{{ $counts['systems'] }}</div>
			<div class="agd-stat-label">{{ $isEn ? 'Systems' : 'Systemen' }}</div>
		</div>
		<div class="agd-stat-card">
			<div class="agd-stat-val">{{ $counts['has_access'] }}</div>
			<div class="agd-stat-label">{{ $isEn ? 'Access grants' : 'Toegangsrechten' }}</div>
		</div>
		<div class="agd-stat-card">
			<div class="agd-stat-val" style="color: #dc2626;">{{ $counts['risks'] }}</div>
			<div class="agd-stat-label">{{ $isEn ? 'Open risks' : 'Open risico\'s' }}</div>
		</div>
	</div>
</div>

<div class="agd-section">
	<div class="agd-section-title">
		<div>
			<h2>{{ $isEn ? '1. The access matrix' : '1. De toegangsmatrix' }}</h2>
			<p>{{ $isEn ? 'Who has access to what — at a glance.' : 'Wie heeft waar toegang — in één oogopslag.' }}</p>
		</div>
	</div>
	<div class="agd-card">
		<div class="agd-matrix-wrap">
			<table class="agd-matrix">
				<thead>
					<tr>
						<th style="text-align:left;">{{ $isEn ? 'Person' : 'Persoon' }}</th>
						@foreach ($systems as $sys)
							<th>{!! str_replace(' (demo)', '', e($sys->name)) !!}</th>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($people as $p)
						@php $isInactive = $p->status !== 'active'; @endphp
						<tr class="{{ $isInactive ? 'agd-person-inactive' : '' }}">
							<td class="agd-person-cell">
								<span class="agd-person-dot"></span>{{ $p->first_name }} {{ $p->last_name }}
								<small>{{ $p->job_title }}</small>
							</td>
							@foreach ($systems as $sys)
								@php
									$cell = $cellMap->get($p->id . ':' . $sys->id);
									$state = $cell?->access_state ?? 'unknown';
									[$bg, $color, $border, $sym] = $stateColor[$state];
								@endphp
								<td>
									<span class="agd-cell" style="background: {{ $bg }}; color: {{ $color }}; border-color: {{ $border }};">
										{{ $sym }}
									</span>
								</td>
							@endforeach
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<p style="margin: 14px 0 0; font-size: 12px; color: var(--agd-muted);">
			{{ $isEn
				? '✓ has access · — no access · ? needs review. Red dot = inactive person (still has grants → risk).'
				: '✓ heeft toegang · — geen toegang · ? moet bekeken worden. Rode stip = inactieve persoon (heeft nog rechten → risico).' }}
		</p>
	</div>
</div>

@if ($cycle)
<div class="agd-section">
	<div class="agd-section-title">
		<div>
			<h2>{{ $isEn ? '2. Active review cycle' : '2. Actieve review-cyclus' }}</h2>
			<p>{{ $cycle->title }} · {{ $isEn ? 'due' : 'deadline' }} {{ \Illuminate\Support\Carbon::parse($cycle->due_at)->format('d-m-Y') }}</p>
		</div>
	</div>
	<div class="agd-card">
		@foreach ($cycleItems as $item)
			<div class="agd-cycle-item">
				<div style="min-width: 0;">
					<div class="agd-cycle-name">{{ $item->person_label }} → {{ str_replace(' (demo)', '', $item->system_label) }}</div>
					<div class="agd-cycle-meta">{{ $isEn ? 'Snapshot state' : 'Snapshot' }}:</div>
				</div>
				<div>
					@php
						$s = $item->snapshot_state;
						$cls = match ($s) {
							'has_access' => 'agd-badge-has',
							'no_access' => 'agd-badge-no',
							'needs_review' => 'agd-badge-review',
							default => 'agd-badge-unknown',
						};
						$label = match ($s) {
							'has_access' => $isEn ? 'has access' : 'heeft toegang',
							'no_access' => $isEn ? 'no access' : 'geen toegang',
							'needs_review' => $isEn ? 'needs review' : 'check nodig',
							default => $isEn ? 'unknown' : 'onbekend',
						};
					@endphp
					<span class="agd-badge {{ $cls }}">{{ $label }}</span>
				</div>
			</div>
		@endforeach
		<p style="margin: 16px 0 0; font-size: 13px; color: var(--agd-muted);">
			{{ $isEn
				? 'In the real app, each row gets a keep / revoke / change decision. Completing the cycle produces follow-up actions for IT and a PDF report for auditors.'
				: 'In de echte app krijgt elke regel een beslissing: keep / revoke / change. Als je de cyclus afrondt ontstaan follow-up acties voor IT en een PDF-rapport voor auditors.' }}
		</p>
	</div>
</div>
@endif

<div class="agd-section">
	<div class="agd-section-title">
		<div>
			<h2>{{ $isEn ? '3. Risks detected automatically' : '3. Risico\'s automatisch gespot' }}</h2>
			<p>{{ $isEn ? 'The scanner looks for orphan access, stale admins, overdue reviews and more.' : 'De scanner zoekt naar verweesd toegang, stale admins, te oude reviews en meer.' }}</p>
		</div>
	</div>
	<div class="agd-card">
		@forelse ($risks as $risk)
			<div class="agd-risk">
				<div class="agd-risk-sev" style="background: {{ $riskColor[$risk->severity] ?? '#64748b' }};">{{ $risk->severity }}</div>
				<div class="agd-risk-body">
					<p class="agd-risk-title">{{ $risk->title }}</p>
					<p class="agd-risk-desc">{{ $risk->description }}</p>
					<span class="agd-risk-kind">{{ str_replace('_', ' ', $risk->kind) }}</span>
				</div>
			</div>
		@empty
			<p style="color: var(--agd-muted);">{{ $isEn ? 'No open risks at the moment.' : 'Geen open risico\'s op dit moment.' }}</p>
		@endforelse
	</div>
</div>

<div class="agd-section">
	<div class="agd-cta-final">
		<h2>{{ $isEn ? 'Try it with your own data.' : 'Probeer het met je eigen data.' }}</h2>
		<p>{{ $isEn
			? '14 days free. No credit card. CSV import, screenshot-to-matrix and AI explanations included. Takes ~10 minutes to map your first systems.'
			: '14 dagen gratis. Geen creditcard. CSV-import, screenshot-naar-matrix en AI-uitleg inclusief. Je eerste systemen mapt in ongeveer 10 minuten.' }}</p>
		<div class="agd-cta-actions">
			<a href="{{ route('register', ['locale' => $locale]) }}" class="agd-btn agd-btn-primary">
				{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
				<svg width="14" height="14" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
			<a href="{{ route('accessguard.landing', ['locale' => $locale]) }}" class="agd-btn agd-btn-secondary">
				{{ $isEn ? 'Back to overview' : 'Terug naar overzicht' }}
			</a>
		</div>
	</div>
</div>

</div>
@endsection
