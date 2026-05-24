@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale !== 'nl';
@endphp

@section('title', 'AccessGuard — ' . ($isEn ? 'Know who has access to what' : 'Zeker weten wie waar toegang heeft') . ' — ' . config('app.name'))
@section('description', $isEn
	? 'AccessGuard: the access matrix for SMBs without an IT department. Know exactly who has access to which system, periodic reviews, airtight onboarding and offboarding.'
	: 'AccessGuard: de toegangsmatrix voor MKB zonder IT-afdeling. Zie exact wie waar toegang toe heeft, voer periodieke reviews uit en regel onboarding en offboarding waterdicht.')

@section('content')

<style>
/* ========================================================
   AccessGuard landing — V1-styled (scoped to .ag root)
   Only on /{locale}/accessguard
   ======================================================== */
.ag {
	--ag-bg: #f5f7fb;
	--ag-card: #ffffff;
	--ag-text: #0f172a;
	--ag-muted: rgba(15,23,42,.72);
	--ag-soft: rgba(15,23,42,.55);
	--ag-border: rgba(15,23,42,.10);
	--ag-border-strong: rgba(15,23,42,.18);
	--ag-shadow: 0 14px 40px rgba(15,23,42,.06);
	--ag-shadow-strong: 0 22px 56px rgba(15,23,42,.10);
	--ag-radius: 18px;
	--ag-radius-lg: 22px;
	--ag-primary: #ff7a18;
	--ag-primary-hover: #e86a0f;
	--ag-ok: #138a3d;
	--ag-flag: #b45309;

	color: var(--ag-text);
	font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
.ag-section { padding: 72px 0; }
.ag-section-alt { background: var(--ag-bg); }
.ag-container { max-width: 1160px; margin: 0 auto; padding: 0 24px; }
.ag-stack-sm > * + * { margin-top: 12px; }
.ag-stack-md > * + * { margin-top: 20px; }
.ag-stack-lg > * + * { margin-top: 32px; }

.ag h1, .ag h2, .ag h3, .ag h4 { color: var(--ag-text); letter-spacing: -0.02em; }
.ag-h1 {
	margin: 0;
	font-size: clamp(2.1rem, 3.6vw, 3.15rem);
	line-height: 1.06;
	font-weight: 900;
}
.ag-h2 {
	margin: 0;
	font-size: clamp(1.5rem, 2.4vw, 2rem);
	line-height: 1.15;
	font-weight: 950;
}
.ag-h3 {
	margin: 0;
	font-size: 1.0625rem;
	font-weight: 900;
	line-height: 1.25;
}
.ag-lead {
	margin: 0;
	color: var(--ag-muted);
	font-size: 1.0625rem;
	font-weight: 600;
	line-height: 1.55;
	max-width: 65ch;
}
.ag-p { color: rgba(15,23,42,.82); line-height: 1.65; font-weight: 500; margin: 0; }

.ag-kicker {
	display: inline-block;
	padding: 6px 12px;
	border-radius: 999px;
	background: rgba(255,122,24,.12);
	color: var(--ag-primary);
	font-size: 12px;
	font-weight: 900;
	text-transform: uppercase;
	letter-spacing: 0.05em;
}
.ag-kicker-dark {
	background: rgba(15,23,42,.08);
	color: rgba(15,23,42,.75);
}

.ag-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	padding: 13px 20px;
	border-radius: 14px;
	text-decoration: none;
	font-weight: 850;
	font-size: 0.95rem;
	border: 1px solid transparent;
	transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
	cursor: pointer;
}
.ag-btn-primary { background: var(--ag-primary); color: #fff; box-shadow: 0 10px 24px rgba(255,122,24,.28); }
.ag-btn-primary:hover { background: var(--ag-primary-hover); transform: translateY(-1px); box-shadow: 0 14px 30px rgba(255,122,24,.34); color: #fff; }
.ag-btn-secondary { background: #fff; color: var(--ag-text); border-color: var(--ag-border-strong); }
.ag-btn-secondary:hover { background: rgba(15,23,42,.04); color: var(--ag-text); }
.ag-btn-ghost { background: transparent; color: #fff; border-color: rgba(255,255,255,.30); }
.ag-btn-ghost:hover { background: rgba(255,255,255,.10); color: #fff; }

.ag-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.ag-badge {
	display: inline-flex;
	padding: 6px 12px;
	border-radius: 999px;
	font-size: 12px;
	font-weight: 800;
	background: rgba(15,23,42,.06);
	border: 1px solid var(--ag-border);
	color: rgba(15,23,42,.78);
}

.ag-card {
	background: var(--ag-card);
	border: 1px solid var(--ag-border);
	border-radius: var(--ag-radius);
	padding: 28px;
	box-shadow: var(--ag-shadow);
}
.ag-card-lg { padding: 32px; border-radius: var(--ag-radius-lg); }

/* ---------- HERO ---------- */
.ag-hero {
	background: radial-gradient(900px 420px at 15% 0%, rgba(255,122,24,.10), transparent 60%), #f5f7fb;
	padding: 88px 0 72px;
}
.ag-hero-grid {
	display: grid;
	grid-template-columns: 1.15fr 0.95fr;
	gap: 56px;
	align-items: center;
}
.ag-hero-content > * + * { margin-top: 20px; }
.ag-hero-accent { color: var(--ag-primary); white-space: nowrap; }
.ag-hero-visual { display: flex; justify-content: flex-end; }

.ag-visual-card {
	width: 100%;
	max-width: 520px;
	background: #fff;
	border: 1px solid var(--ag-border);
	border-radius: var(--ag-radius-lg);
	padding: 22px;
	box-shadow: var(--ag-shadow-strong);
}
.ag-visual-head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 14px;
}
.ag-visual-head-title {
	font-weight: 900;
	font-size: 12px;
	color: rgba(15,23,42,.70);
	text-transform: uppercase;
	letter-spacing: 0.06em;
}
.ag-visual-head-status {
	font-size: 12px;
	color: var(--ag-ok);
	font-weight: 800;
	display: inline-flex;
	align-items: center;
	gap: 5px;
}
.ag-visual-head-status::before {
	content: '';
	display: inline-block;
	width: 7px;
	height: 7px;
	border-radius: 999px;
	background: var(--ag-ok);
	animation: ag-pulse 2s infinite;
}
@keyframes ag-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

.ag-matrix {
	border: 1px solid var(--ag-border);
	border-radius: 14px;
	overflow: hidden;
	background: #fff;
}
.ag-matrix-head, .ag-matrix-row {
	display: grid;
	grid-template-columns: 1.4fr repeat(5, 0.48fr);
	gap: 8px;
	padding: 10px 12px;
	align-items: center;
	font-size: 12px;
}
.ag-matrix-head {
	background: rgba(15,23,42,.03);
	color: rgba(15,23,42,.70);
	font-weight: 900;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	font-size: 11px;
}
.ag-matrix-row { border-top: 1px solid var(--ag-border); font-weight: 700; }
.ag-matrix-name {
	font-weight: 800;
	color: rgba(15,23,42,.88);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.ag-matrix-cell {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	border-radius: 7px;
	border: 1px solid;
	font-size: 12px;
	font-weight: 900;
	margin: 0 auto;
}
.ag-cell-ok { background: #dcfce7; color: #15803d; border-color: #86efac; }
.ag-cell-no { background: #e2e8f0; color: #475569; border-color: #cbd5e1; }
.ag-cell-flag { background: #fef3c7; color: #b45309; border-color: #fcd34d; }
.ag-cell-unknown { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; }
.ag-matrix-foot {
	margin-top: 14px;
	padding-top: 12px;
	border-top: 1px solid var(--ag-border);
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 11.5px;
	color: rgba(15,23,42,.62);
	font-weight: 600;
	gap: 12px;
}
.ag-matrix-foot strong { color: var(--ag-flag); font-weight: 900; white-space: nowrap; }

/* ---------- PROBLEM / WHY ---------- */
.ag-problems {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 20px;
	margin-top: 40px;
}
.ag-problem-card {
	background: #fff;
	border: 1px solid var(--ag-border);
	border-radius: var(--ag-radius);
	padding: 28px;
	box-shadow: var(--ag-shadow);
}
.ag-problem-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border-radius: 12px;
	background: rgba(220,38,38,.10);
	color: #b91c1c;
	font-size: 18px;
	font-weight: 900;
	margin-bottom: 16px;
}

/* ---------- FEATURES ---------- */
.ag-features-grid {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 20px;
	margin-top: 40px;
}
.ag-feature-card {
	background: #fff;
	border: 1px solid var(--ag-border);
	border-radius: var(--ag-radius);
	padding: 28px;
	box-shadow: var(--ag-shadow);
	transition: transform .18s ease, box-shadow .18s ease;
}
.ag-feature-card:hover { transform: translateY(-2px); box-shadow: var(--ag-shadow-strong); }
.ag-feature-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	border-radius: 12px;
	background: rgba(255,122,24,.10);
	color: var(--ag-primary);
	font-size: 20px;
	font-weight: 900;
	margin-bottom: 16px;
}

/* ---------- HOW IT WORKS ---------- */
.ag-steps {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 20px;
	margin-top: 40px;
}
.ag-step {
	background: #fff;
	border: 1px solid var(--ag-border);
	border-radius: var(--ag-radius);
	padding: 28px;
	box-shadow: var(--ag-shadow);
}
.ag-step-n {
	font-size: 2.75rem;
	font-weight: 950;
	line-height: 1;
	color: var(--ag-primary);
	letter-spacing: -0.03em;
	margin-bottom: 14px;
	display: block;
}

/* ---------- WHO IS THIS FOR ---------- */
.ag-who {
	display: grid;
	grid-template-columns: 1fr 1.15fr;
	gap: 48px;
	align-items: center;
}
.ag-check-list { margin: 0; padding: 0; list-style: none; }
.ag-check-list li {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	padding: 14px 0;
	border-bottom: 1px solid var(--ag-border);
	font-weight: 600;
	color: rgba(15,23,42,.82);
	line-height: 1.5;
}
.ag-check-list li:last-child { border-bottom: none; }
.ag-check {
	flex-shrink: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 22px;
	height: 22px;
	border-radius: 999px;
	background: rgba(19,138,61,.12);
	color: var(--ag-ok);
	margin-top: 2px;
}
.ag-check svg { width: 13px; height: 13px; }
.ag-who-title {
	margin: 0 0 20px;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	font-size: 12px;
	color: rgba(15,23,42,.60);
	font-weight: 900;
}

/* ---------- PRICING TEASER ---------- */
.ag-pricing-teaser {
	text-align: center;
	max-width: 760px;
	margin: 0 auto;
}
.ag-pricing-teaser > * + * { margin-top: 20px; }

/* ---------- FINAL CTA ---------- */
.ag-final-cta {
	background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
	color: #fff;
	padding: 88px 0;
	text-align: center;
}
.ag-final-cta h2 { color: #fff; }
.ag-final-cta .ag-lead { color: rgba(255,255,255,.75); margin-left: auto; margin-right: auto; }
.ag-final-cta-inner { max-width: 720px; margin: 0 auto; }
.ag-final-cta-inner > * + * { margin-top: 24px; }

.ag-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.ag-actions-center { justify-content: center; }

/* ---------- BREADCRUMB ---------- */
.ag-crumbs {
	font-size: 13px;
	color: rgba(15,23,42,.55);
	margin-bottom: 32px;
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
}
.ag-crumbs a { color: rgba(15,23,42,.70); text-decoration: none; font-weight: 600; }
.ag-crumbs a:hover { color: var(--ag-primary); }
.ag-crumbs .sep { opacity: 0.4; }
.ag-crumbs .current { color: var(--ag-text); font-weight: 700; }

/* ---------- RESPONSIVE ---------- */
@media (max-width: 980px) {
	.ag-section { padding: 56px 0; }
	.ag-hero { padding: 64px 0 48px; }
	.ag-hero-grid { grid-template-columns: 1fr; gap: 40px; }
	.ag-hero-visual { justify-content: flex-start; }
	.ag-problems { grid-template-columns: 1fr; }
	.ag-features-grid { grid-template-columns: 1fr 1fr; }
	.ag-steps { grid-template-columns: 1fr 1fr; }
	.ag-who { grid-template-columns: 1fr; gap: 32px; }
	.ag-card, .ag-feature-card, .ag-step, .ag-problem-card { padding: 24px; }
}
@media (max-width: 640px) {
	.ag-section { padding: 48px 0; }
	.ag-hero { padding: 56px 0 40px; }
	.ag-features-grid, .ag-steps { grid-template-columns: 1fr; }
	.ag-matrix-head, .ag-matrix-row { grid-template-columns: 1.2fr repeat(5, 0.38fr); gap: 4px; padding: 8px 10px; }
	.ag-matrix-cell { width: 22px; height: 22px; font-size: 11px; }
	.ag-final-cta { padding: 64px 0; }
}
</style>

<div class="ag">

{{-- HERO --}}
<section class="ag-hero">
	<div class="ag-container">
		<nav class="ag-crumbs">
			<a href="{{ route('home') }}">{{ __('Home') }}</a>
			<span class="sep">/</span>
			<a href="{{ route('tools.index', ['locale' => $locale]) }}">Tools</a>
			<span class="sep">/</span>
			<span class="current">AccessGuard</span>
		</nav>

		<div class="ag-hero-grid">
			<div class="ag-hero-content">
				<span class="ag-kicker">{{ $isEn ? 'Access & identity · Pro plan' : 'Toegang & identity · Pro plan' }}</span>

				<h1 class="ag-h1">
					{{ $isEn ? 'Know exactly who has' : 'Zeker weten wie waar' }}<br>
					<span class="ag-hero-accent">{{ $isEn ? 'access to what.' : 'toegang heeft.' }}</span>
				</h1>

				<p class="ag-lead">
					{{ $isEn
						? 'Access management for SMBs without an IT department. For accounts, suppliers, keys and everything you don\'t want to forget. One overview, a clear process, audit-ready out of the box.'
						: 'Toegangsbeheer voor MKB zonder IT-afdeling. Voor accounts, leveranciers, sleutels en alles wat je niet wilt vergeten. Eén overzicht, een helder proces, audit-ready zonder extra werk.' }}
				</p>

				<div class="ag-actions">
					@if ($hasAccess)
						<a href="{{ route('tools.accessguard.index', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
							{{ $isEn ? 'Open AccessGuard' : 'Open AccessGuard' }}
							<svg width="16" height="16" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</a>
					@elseif ($isAuthed)
						<a href="{{ route('pricing', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
							{{ $isEn ? 'Upgrade to Pro' : 'Upgrade naar Pro' }}
						</a>
						<a href="#how" class="ag-btn ag-btn-secondary">{{ $isEn ? 'See how it works' : 'Bekijk hoe het werkt' }}</a>
					@else
						<a href="{{ route('register', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
							{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
						</a>
						<a href="{{ route('accessguard.demo', ['locale' => $locale]) }}" class="ag-btn ag-btn-secondary">
							{{ $isEn ? 'Try without signup' : 'Probeer zonder account' }}
						</a>
					@endif
				</div>

				<div class="ag-badges">
					@foreach ([
						$isEn ? 'Audit trail' : 'Audit trail',
						$isEn ? 'Offboarding' : 'Offboarding',
						$isEn ? 'Vault (encrypted)' : 'Vault (versleuteld)',
						$isEn ? 'Risk detection' : 'Risico-detectie',
						$isEn ? 'Reminders' : 'Reminders',
						$isEn ? 'AI explanations' : 'AI-uitleg',
					] as $badge)
						<span class="ag-badge">{{ $badge }}</span>
					@endforeach
				</div>
			</div>

			<div class="ag-hero-visual">
				<div class="ag-visual-card">
					<div class="ag-visual-head">
						<span class="ag-visual-head-title">{{ $isEn ? 'Access Matrix' : 'Access Matrix' }}</span>
						<span class="ag-visual-head-status">{{ $isEn ? 'live' : 'live' }}</span>
					</div>
					<div class="ag-matrix">
						<div class="ag-matrix-head">
							<span>{{ $isEn ? 'Person' : 'Persoon' }}</span>
							<span style="text-align:center">M365</span>
							<span style="text-align:center">Slack</span>
							<span style="text-align:center">AWS</span>
							<span style="text-align:center">SF</span>
							<span style="text-align:center">1P</span>
						</div>
						@foreach ([
							['Jan de Vries',       ['ok','ok','ok','ok','flag']],
							['Lisa Jansen',        ['ok','ok','no','ok','ok']],
							['Mohammed El Amrani', ['ok','ok','flag','no','no']],
							['Sophie van Dijk',    ['ok','ok','no','ok','no']],
							['Patrick Smit',       ['no','no','no','unknown','unknown']],
						] as $row)
							<div class="ag-matrix-row">
								<span class="ag-matrix-name">{{ $row[0] }}</span>
								@foreach ($row[1] as $state)
									<span class="ag-matrix-cell ag-cell-{{ $state }}">
										{!! ['ok' => '✓', 'no' => '×', 'flag' => '?', 'unknown' => '—'][$state] !!}
									</span>
								@endforeach
							</div>
						@endforeach
					</div>
					<div class="ag-matrix-foot">
						<span>✓ {{ $isEn ? 'access' : 'toegang' }} · × {{ $isEn ? 'none' : 'geen' }} · ? {{ $isEn ? 'review' : 'review' }} · — {{ $isEn ? 'unknown' : 'onbekend' }}</span>
						<strong>⚠ 2 {{ $isEn ? 'open risks' : 'open risico\'s' }}</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- PROBLEM / WHY --}}
<section class="ag-section">
	<div class="ag-container">
		<div class="ag-stack-md" style="max-width: 720px;">
			<span class="ag-kicker ag-kicker-dark">{{ $isEn ? 'Why this matters' : 'Waarom dit ertoe doet' }}</span>
			<h2 class="ag-h2">
				{{ $isEn
					? 'Access chaos is invisible — until someone leaves.'
					: 'Toegangschaos zie je niet — tot er iemand weggaat.' }}
			</h2>
		</div>

		<div class="ag-problems">
			@foreach ([
				['t_nl' => 'Excel, mail, WhatsApp', 't_en' => 'Excel, email, WhatsApp',
				 'd_nl' => 'Wie heeft toegang tot Salesforce? Wie had ook al weer de alarmcode? Kennis zit verspreid in documenten en hoofden.',
				 'd_en' => 'Who has access to Salesforce? Who had the alarm code? Knowledge lives scattered across docs and people\'s heads.'],
				['t_nl' => 'Offboarding vergeten', 't_en' => 'Missed offboarding',
				 'd_nl' => 'Ex-medewerkers houden toegang tot systemen lang nadat ze weg zijn. Security-incident of privacy-risico wacht op gebeuren.',
				 'd_en' => 'Former staff retain access to systems long after leaving. A security or privacy incident waiting to happen.'],
				['t_nl' => 'Geen bewijs', 't_en' => 'No audit trail',
				 'd_nl' => 'Bij een audit of incident kan je niet aantonen wie wat wanneer mocht. Iedereen wijst naar iemand anders.',
				 'd_en' => 'In an audit or incident you cannot prove who had what access when. Everyone points at someone else.'],
			] as $p)
				<div class="ag-problem-card ag-stack-sm">
					<div class="ag-problem-icon">✕</div>
					<h3 class="ag-h3">{{ $isEn ? $p['t_en'] : $p['t_nl'] }}</h3>
					<p class="ag-p">{{ $isEn ? $p['d_en'] : $p['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- FEATURES --}}
<section class="ag-section ag-section-alt" id="features">
	<div class="ag-container">
		<div class="ag-stack-md" style="max-width: 780px;">
			<span class="ag-kicker">{{ $isEn ? 'What AccessGuard gives you' : 'Wat AccessGuard je geeft' }}</span>
			<h2 class="ag-h2">
				{{ $isEn
					? 'One overview. A clear process. Audit-ready.'
					: 'Eén overzicht. Een helder proces. Audit-ready.' }}
			</h2>
			<p class="ag-lead">
				{{ $isEn
					? 'Seven integrated layers, built in the sequence that real SMBs operate in — not a feature list from a security vendor.'
					: 'Zeven geïntegreerde lagen, in de volgorde gebouwd waarop een MKB-bedrijf echt werkt — geen feature-lijst van een security-leverancier.' }}
			</p>
		</div>

		<div class="ag-features-grid">
			@foreach ([
				['icon' => '◈', 't_nl' => 'Access Matrix', 't_en' => 'Access Matrix',
				 'd_nl' => 'People × systemen in één overzicht. Klik op een cel om de status in één klik door te rouleren (toegang / geen / review / onbekend).',
				 'd_en' => 'People × systems at a glance. Click a cell to cycle the status in one click (access / none / review / unknown).'],
				['icon' => '◇', 't_nl' => 'Fijnmazige items', 't_en' => 'Fine-grained items',
				 'd_nl' => 'Per systeem definieer je rollen, licenties en accounts. "Admin op Salesforce" vs "Read-only" — de cel aggregeert automatisch.',
				 'd_en' => 'Per system you define roles, licences and accounts. "Salesforce Admin" vs "Read-only" — the cell aggregates automatically.'],
				['icon' => '⟲', 't_nl' => 'Periodieke reviews', 't_en' => 'Periodic reviews',
				 'd_nl' => 'Start een review-cyclus: de matrix wordt gesnapshot. Per regel keep/revoke/change. Bij completion: intrekkingsacties voor IT.',
				 'd_en' => 'Kick off a review cycle: the matrix is snapshotted. Decide keep/revoke/change per row. On completion IT gets revoke actions.'],
				['icon' => '→', 't_nl' => 'Onboarding + offboarding', 't_en' => 'Onboarding + offboarding',
				 'd_nl' => 'Checklist per fase, bewijs-uploads (PDF/JPG), automatische status-transities. Offboarding triggert automatisch revoke-acties voor alle toegang.',
				 'd_en' => 'Checklist per phase, evidence uploads (PDF/JPG), automatic status transitions. Offboarding auto-triggers revoke actions for every access.'],
				['icon' => '⚠', 't_nl' => 'Risico-detectie', 't_en' => 'Risk detection',
				 'd_nl' => 'Elke nacht scant een cron op patronen: stale admins (>90d niet verified), orphan access na uitdienst, overdue reviews, excessive access.',
				 'd_en' => 'Nightly cron scans for patterns: stale admins (>90d unverified), orphan access after leaving, overdue reviews, excessive access.'],
				['icon' => '🔐', 't_nl' => 'Vault met audit-log', 't_en' => 'Vault with audit log',
				 'd_nl' => 'Wachtwoorden, tokens, API-keys en SSH-keys versleuteld opgeslagen (AES-256). Per-user ACL. Elke reveal/decrypt wordt gelogd.',
				 'd_en' => 'Passwords, tokens, API keys and SSH keys stored encrypted (AES-256). Per-user ACL. Every reveal/decrypt is logged.'],
				['icon' => '🤖', 't_nl' => 'AI-uitleg', 't_en' => 'AI explanations',
				 'd_nl' => 'Niet-security-mensen snappen waarom een risico belangrijk is. AI bouwt tenant-veilige context en legt uit + geeft concrete vervolgstappen.',
				 'd_en' => 'Non-security users understand why a risk matters. AI builds tenant-safe context and explains + gives concrete next steps.'],
				['icon' => '🔔', 't_nl' => 'Reminders', 't_en' => 'Reminders',
				 'd_nl' => 'Deadlines van cycli + processen, overdue acties, aankomende start/einddatums. Allemaal geaggregeerd op het dashboard.',
				 'd_en' => 'Cycle and process deadlines, overdue actions, upcoming start/end dates. All aggregated on the dashboard.'],
				['icon' => '📋', 't_nl' => 'Append-only log', 't_en' => 'Append-only log',
				 'd_nl' => 'Elke decision, elke state-change, elke ACL-grant landt in een audit-log met JSON-payload. Nooit updates, altijd spoor.',
				 'd_en' => 'Every decision, state change and ACL grant lands in an audit log with JSON payload. No updates, full trail.'],
			] as $f)
				<div class="ag-feature-card ag-stack-sm">
					<div class="ag-feature-icon">{{ $f['icon'] }}</div>
					<h3 class="ag-h3">{{ $isEn ? $f['t_en'] : $f['t_nl'] }}</h3>
					<p class="ag-p">{{ $isEn ? $f['d_en'] : $f['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- HOW IT WORKS --}}
<section class="ag-section" id="how">
	<div class="ag-container">
		<div class="ag-stack-md" style="max-width: 720px;">
			<span class="ag-kicker ag-kicker-dark">{{ $isEn ? 'How it works' : 'Hoe het werkt' }}</span>
			<h2 class="ag-h2">
				{{ $isEn ? 'From chaos to grip in four steps.' : 'Van chaos naar grip in vier stappen.' }}
			</h2>
		</div>

		<div class="ag-steps">
			@foreach ([
				['n' => '01', 't_nl' => 'Inventariseer', 't_en' => 'Inventory',
				 'd_nl' => 'Voer personen en systemen in. Optioneel: definieer items (rollen, licenties) per systeem.',
				 'd_en' => 'Add people and systems. Optionally define items (roles, licences) per system.'],
				['n' => '02', 't_nl' => 'Vul de matrix', 't_en' => 'Fill the matrix',
				 'd_nl' => 'Klik per cel de huidige status aan. Zichtbaar wordt wie overal te veel, te weinig of onbekend toegang heeft.',
				 'd_en' => 'Click each cell\'s current status. You\'ll see at a glance who has excessive, missing or unknown access.'],
				['n' => '03', 't_nl' => 'Review periodiek', 't_en' => 'Review periodically',
				 'd_nl' => 'Elke quarter (of jaarlijks) een review-cyclus: per rij keep/revoke/change. Acties volgen automatisch.',
				 'd_en' => 'Each quarter (or yearly) a review cycle: keep/revoke/change per row. Actions follow automatically.'],
				['n' => '04', 't_nl' => 'Proces voor wijzigingen', 't_en' => 'Process for changes',
				 'd_nl' => 'Nieuwe medewerker? Onboarding. Iemand weg? Offboarding met automatische revoke. Alles met bewijs en log.',
				 'd_en' => 'New hire? Onboarding. Someone leaving? Offboarding with automatic revoke. All with evidence and an audit log.'],
			] as $s)
				<div class="ag-step ag-stack-sm">
					<span class="ag-step-n">{{ $s['n'] }}</span>
					<h3 class="ag-h3">{{ $isEn ? $s['t_en'] : $s['t_nl'] }}</h3>
					<p class="ag-p">{{ $isEn ? $s['d_en'] : $s['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- WHO IS THIS FOR --}}
<section class="ag-section ag-section-alt">
	<div class="ag-container">
		<div class="ag-who">
			<div class="ag-stack-md">
				<span class="ag-kicker">{{ $isEn ? 'Who is this for' : 'Voor wie' }}</span>
				<h2 class="ag-h2">
					{{ $isEn ? 'For teams that want control without an IT department.' : 'Voor teams die grip willen houden zonder IT-afdeling.' }}
				</h2>
				<p class="ag-p">
					{{ $isEn
						? 'Most access management tools assume a dedicated IAM team. AccessGuard is built for 10–200 person SMBs where the office manager, HR lead or operational director keeps track.'
						: 'De meeste access-management tools gaan uit van een dedicated IAM-team. AccessGuard is gebouwd voor 10–200-persoons MKB waar de office manager, HR-lead of operationeel directeur het bijhoudt.' }}
				</p>
				<p class="ag-p">
					{{ $isEn
						? 'You don\'t need to connect every system. Start by writing down what you already know; work step by step toward complete coverage.'
						: 'Je hoeft niet elk systeem te koppelen. Begin met vastleggen wat je nu al weet; werk stap voor stap naar complete dekking.' }}
				</p>
			</div>

			<div class="ag-card ag-card-lg">
				<h3 class="ag-who-title">{{ $isEn ? 'Typical fit' : 'Typische fit' }}</h3>
				<ul class="ag-check-list">
					@foreach ([
						['nl' => 'Organisaties van 10–200 medewerkers', 'en' => 'Organisations of 10–200 staff'],
						['nl' => 'Geen dedicated IT-afdeling of IAM-team', 'en' => 'No dedicated IT or IAM team'],
						['nl' => 'Gebruik van 5–30 SaaS-systemen (M365, Slack, Salesforce, Exact, etc.)', 'en' => 'Using 5–30 SaaS systems (M365, Slack, Salesforce, Exact, etc.)'],
						['nl' => 'Compliance-eisen (ISO 27001, NEN 7510, GDPR-audit) die bewijs vragen', 'en' => 'Compliance requirements (ISO 27001, NEN 7510, GDPR audits) that demand proof'],
						['nl' => 'Externe leveranciers + tijdelijke krachten die ook toegang krijgen', 'en' => 'External suppliers + temporary staff that also get access'],
					] as $item)
						<li>
							<span class="ag-check">
								<svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span>{{ $isEn ? $item['en'] : $item['nl'] }}</span>
						</li>
					@endforeach
				</ul>
			</div>
		</div>
	</div>
</section>

{{-- PRICING TEASER --}}
<section class="ag-section">
	<div class="ag-container">
		<div class="ag-pricing-teaser">
			<span class="ag-kicker ag-kicker-dark">{{ $isEn ? 'Pricing' : 'Prijzen' }}</span>
			<h2 class="ag-h2">
				{{ $isEn ? 'Included from Pro.' : 'Inbegrepen vanaf Pro.' }}
			</h2>
			<p class="ag-lead" style="margin: 0 auto;">
				{{ $isEn
					? 'AccessGuard is included in the Pro plan (€12/month) with a 14-day free trial. Business adds multi-user access and higher limits. Free plan lets you preview, but managing access requires Pro.'
					: 'AccessGuard is inbegrepen vanaf het Pro-plan (€12/maand) met 14 dagen gratis proef. Business voegt multi-user toegang en hogere limieten toe. Het gratis plan laat je kijken, maar beheer vereist Pro.' }}
			</p>
			<div class="ag-actions ag-actions-center">
				<a href="{{ route('pricing', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
					{{ $isEn ? 'See all pricing' : 'Bekijk alle prijzen' }}
				</a>
				<a href="{{ route('accessguard.handleiding', ['locale' => $locale]) }}" class="ag-btn ag-btn-secondary" target="_blank">
					📄 {{ $isEn ? 'Download manual (PDF)' : 'Download handleiding (PDF)' }}
				</a>
				@if (! $isAuthed)
					<a href="{{ route('register', ['locale' => $locale]) }}" class="ag-btn ag-btn-secondary">
						{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
					</a>
				@endif
			</div>
		</div>
	</div>
</section>

{{-- READING / KENNISBANK — blog cross-links --}}
<section class="ag-section ag-section-alt" id="blog-links">
	<div class="ag-container">
		<div class="ag-stack-md" style="max-width: 760px;">
			<span class="ag-kicker">{{ $isEn ? 'Further reading' : 'Verder lezen' }}</span>
			<h2 class="ag-h2">{{ $isEn ? 'Deep dives, not demos.' : 'Eerst alles weten? Begin hier.' }}</h2>
			<p class="ag-lead">
				{{ $isEn
					? 'Our guide library covers access management end-to-end — from the first matrix to M365 directory sync, ISO 27001 Annex A.9, and quarterly review rhythms.'
					: 'Onze gids-bibliotheek behandelt toegangsbeheer van begin tot eind — van de eerste matrix tot M365 directory-sync, ISO 27001 Annex A.9, en kwartaal-review-ritmes.' }}
			</p>
		</div>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 28px;">
			<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => 'toegangsbeheer-mkb-complete-gids']) }}"
				style="background: #fff; border: 1px solid rgba(15,23,42,.10); border-radius: 14px; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 8px;">
				<span class="ag-kicker" style="align-self: flex-start;">★ {{ __('Pillar-gids') }}</span>
				<strong style="font-size: 1.0625rem; line-height: 1.3;">{{ __('Toegangsbeheer voor het MKB — complete gids') }}</strong>
				<span style="font-size: 13px; color: rgba(15,23,42,.62);">{{ __('Van je eerste matrix tot directory-sync, reviews en automatisering.') }}</span>
			</a>
			<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => 'iso-27001-annex-a9-toegangsbeheer']) }}"
				style="background: #fff; border: 1px solid rgba(15,23,42,.10); border-radius: 14px; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 8px;">
				<span class="ag-kicker" style="align-self: flex-start;">{{ __('Compliance') }}</span>
				<strong style="font-size: 1.0625rem; line-height: 1.3;">{{ __('ISO 27001 Annex A.9: wat de auditor wil zien') }}</strong>
				<span style="font-size: 13px; color: rgba(15,23,42,.62);">{{ __('Bewijsvoering per sub-control, inclusief access reviews.') }}</span>
			</a>
			<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => 'waterdichte-offboarding-stappen']) }}"
				style="background: #fff; border: 1px solid rgba(15,23,42,.10); border-radius: 14px; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 8px;">
				<span class="ag-kicker" style="align-self: flex-start;">{{ __('Offboarding') }}</span>
				<strong style="font-size: 1.0625rem; line-height: 1.3;">{{ __('Waterdichte offboarding in 12 stappen') }}</strong>
				<span style="font-size: 13px; color: rgba(15,23,42,.62);">{{ __('Checklist met termijnen, verantwoordelijken en valkuilen.') }}</span>
			</a>
			<a href="{{ route('blog.show', ['locale' => $locale, 'slug' => 'm365-entra-id-governance-mkb']) }}"
				style="background: #fff; border: 1px solid rgba(15,23,42,.10); border-radius: 14px; padding: 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 8px;">
				<span class="ag-kicker" style="align-self: flex-start;">{{ __('M365 governance') }}</span>
				<strong style="font-size: 1.0625rem; line-height: 1.3;">{{ __('Microsoft 365 governance voor het MKB') }}</strong>
				<span style="font-size: 13px; color: rgba(15,23,42,.62);">{{ __('MFA, Conditional Access, Intune, licenties — het pragmatische minimum.') }}</span>
			</a>
		</div>
		<div style="text-align: center; margin-top: 28px;">
			<a href="{{ route('blog.category', ['locale' => $locale, 'categorySlug' => 'toegangsbeheer']) }}"
				class="ag-btn ag-btn-secondary">
				{{ $isEn ? 'All articles on access management' : 'Alle artikelen over toegangsbeheer' }}
				<svg width="16" height="16" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</a>
		</div>
	</div>
</section>

{{-- FINAL CTA --}}
<section class="ag-final-cta">
	<div class="ag-container">
		<div class="ag-final-cta-inner">
			<h2 class="ag-h2">
				{{ $isEn ? 'Stop guessing. Start knowing.' : 'Stop met gissen. Begin met weten.' }}
			</h2>
			<p class="ag-lead">
				{{ $isEn
					? 'In a quarter of an hour you have your first matrix filled in. The rest follows naturally — review cycles, offboarding, evidence and proof.'
					: 'In een kwartier heb je je eerste matrix ingevuld. De rest volgt vanzelf — review-cycli, offboarding, bewijs en audit-spoor.' }}
			</p>
			<div class="ag-actions ag-actions-center">
				@if ($hasAccess)
					<a href="{{ route('tools.accessguard.index', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
						{{ $isEn ? 'Open AccessGuard' : 'Open AccessGuard' }}
					</a>
				@elseif ($isAuthed)
					<a href="{{ route('pricing', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
						{{ $isEn ? 'Upgrade to Pro' : 'Upgrade naar Pro' }}
					</a>
				@else
					<a href="{{ route('register', ['locale' => $locale]) }}" class="ag-btn ag-btn-primary">
						{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
					</a>
					<a href="/{{ $locale }}/contact?topic=accessguard" class="ag-btn ag-btn-ghost">
						{{ $isEn ? 'Talk to us' : 'Plan een gesprek' }}
					</a>
				@endif
			</div>
		</div>
	</div>
</section>

</div>

@endsection
