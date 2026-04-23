@extends('layouts.app')

@php
	$locale = app()->getLocale();
	$isEn = $locale === 'en';
@endphp

@section('title', 'AccessGuard — ' . ($isEn ? 'Know who has access to what' : 'Zeker weten wie waar toegang heeft') . ' — ' . config('app.name'))
@section('description', $isEn
	? 'AccessGuard: the access matrix for SMBs without an IT department. Know exactly who has access to which system, periodic reviews, airtight onboarding and offboarding.'
	: 'AccessGuard: de toegangsmatrix voor MKB zonder IT-afdeling. Zie exact wie waar toegang toe heeft, voer periodieke reviews uit en regel onboarding en offboarding waterdicht.')

@section('content')

{{-- HERO --}}
<section class="section-dark relative overflow-hidden">
	<div class="absolute inset-0 grid-pattern opacity-40"></div>
	<div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-[color:var(--color-accent)] opacity-10 blur-3xl"></div>

	<div class="relative max-w-[1200px] mx-auto px-6 py-20 grid lg:grid-cols-[1.1fr_1fr] gap-12 items-center">
		<div>
			<nav class="text-sm text-[color:var(--color-on-dark-soft)] mb-5 flex items-center gap-2">
				<a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
				<span class="opacity-40">/</span>
				<a href="{{ route('tools.index', ['locale' => $locale]) }}" class="hover:text-white">Tools</a>
				<span class="opacity-40">/</span>
				<span class="text-[color:var(--color-on-dark-muted)]">AccessGuard</span>
			</nav>

			<span class="pill pill-dark mb-6">{{ $isEn ? 'Access & identity' : 'Toegang & identity' }} · {{ $isEn ? 'Pro plan' : 'Pro plan' }}</span>

			<h1 class="display-1 mb-5">
				{{ $isEn ? 'Know exactly who has' : 'Zeker weten wie waar' }}
				<span class="accent-word">{{ $isEn ? 'access to what.' : 'toegang heeft.' }}</span>
			</h1>

			<p class="text-lg text-[color:var(--color-on-dark-muted)] leading-relaxed max-w-xl mb-8">
				{{ $isEn
					? 'Access management for SMBs without an IT department. For accounts, suppliers, keys and everything you don\'t want to forget. One overview, a clear process, audit-ready out of the box.'
					: 'Toegangsbeheer voor MKB zonder IT-afdeling. Voor accounts, leveranciers, sleutels en alles wat je niet wilt vergeten. Eén overzicht, een helder proces, audit-ready zonder extra werk.' }}
			</p>

			<div class="flex flex-wrap gap-3 mb-6">
				@if ($hasAccess)
					<a href="{{ route('tools.accessguard.index', ['locale' => $locale]) }}" class="btn-accent">
						{{ $isEn ? 'Open AccessGuard' : 'Open AccessGuard' }}
						<svg class="w-4 h-4" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 6h10M7 2l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				@elseif ($isAuthed)
					<a href="{{ route('pricing', ['locale' => $locale]) }}" class="btn-accent">
						{{ $isEn ? 'Upgrade to Pro' : 'Upgrade naar Pro' }}
					</a>
					<a href="#how" class="btn-ghost-light">{{ $isEn ? 'See how it works' : 'Bekijk hoe het werkt' }}</a>
				@else
					<a href="{{ route('register', ['locale' => $locale]) }}" class="btn-accent">
						{{ $isEn ? 'Start free trial' : 'Start een proef' }}
					</a>
					<a href="#how" class="btn-ghost-light">{{ $isEn ? 'See how it works' : 'Bekijk hoe het werkt' }}</a>
				@endif
			</div>

			<div class="flex flex-wrap gap-2 text-xs">
				@foreach ([
					$isEn ? 'Audit trail' : 'Audit trail',
					$isEn ? 'Offboarding' : 'Offboarding',
					$isEn ? 'Vault (encrypted)' : 'Vault (versleuteld)',
					$isEn ? 'Risk detection' : 'Risico-detectie',
					$isEn ? 'Reminders' : 'Reminders',
					$isEn ? 'AI explanations' : 'AI-uitleg',
				] as $badge)
					<span class="pill pill-dark">{{ $badge }}</span>
				@endforeach
			</div>
		</div>

		{{-- Visual: mock access-matrix --}}
		<div class="relative">
			<div class="card bg-white/95 text-[color:var(--color-ink)]" style="backdrop-filter:blur(8px)">
				<div class="flex items-center justify-between mb-4">
					<div class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)] font-bold">{{ $isEn ? 'Access Matrix' : 'Access Matrix' }}</div>
					<span class="text-xs text-emerald-700 font-semibold">● {{ $isEn ? 'live' : 'live' }}</span>
				</div>
				<table class="w-full text-xs">
					<thead>
						<tr class="border-b border-[color:var(--color-line)] text-[color:var(--color-ink-muted)]">
							<th class="text-left py-1 pr-2 font-semibold">{{ $isEn ? 'Person' : 'Persoon' }}</th>
							<th class="text-center py-1 px-1 font-semibold">M365</th>
							<th class="text-center py-1 px-1 font-semibold">Slack</th>
							<th class="text-center py-1 px-1 font-semibold">AWS</th>
							<th class="text-center py-1 px-1 font-semibold">SF</th>
							<th class="text-center py-1 px-1 font-semibold">1P</th>
						</tr>
					</thead>
					<tbody class="[&_td]:py-1.5 [&_td]:px-0 [&_td]:align-middle">
						@foreach ([
							['Jan de Vries',     ['✓','✓','✓','✓','?']],
							['Lisa Jansen',      ['✓','✓','—','✓','✓']],
							['Mohammed El Amrani', ['✓','✓','?','—','—']],
							['Sophie van Dijk',  ['✓','✓','—','✓','—']],
							['Patrick Smit',     ['×','×','×','—','—']],
						] as $row)
							<tr class="border-b border-[color:var(--color-line)]/50">
								<td class="text-left pr-2 font-semibold truncate" style="max-width:130px">{{ $row[0] }}</td>
								@foreach ($row[1] as $state)
									@php
										$cls = match($state) {
											'✓' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
											'×' => 'bg-slate-300 text-slate-700 border-slate-400',
											'?' => 'bg-amber-100 text-amber-800 border-amber-300',
											default => 'bg-slate-100 text-slate-500 border-slate-200',
										};
									@endphp
									<td class="text-center px-0.5">
										<span class="inline-flex items-center justify-center w-6 h-6 rounded border text-xs font-bold {{ $cls }}">{{ $state }}</span>
									</td>
								@endforeach
							</tr>
						@endforeach
					</tbody>
				</table>
				<div class="mt-4 pt-3 border-t border-[color:var(--color-line)] flex items-center justify-between text-[10px] text-[color:var(--color-ink-soft)]">
					<span>✓ {{ $isEn ? 'access' : 'toegang' }} · × {{ $isEn ? 'none' : 'geen' }} · ? {{ $isEn ? 'review' : 'review' }} · — {{ $isEn ? 'unknown' : 'onbekend' }}</span>
					<span class="font-semibold text-amber-700">⚠ 2 {{ $isEn ? 'open risks' : 'open risico\'s' }}</span>
				</div>
			</div>
		</div>
	</div>
</section>

{{-- PROBLEM / WHY --}}
<section class="py-16 bg-white">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'Why this matters' : 'Waarom dit ertoe doet' }}</span>
		<h2 class="display-2 mb-10 max-w-3xl">
			{{ $isEn
				? 'Access chaos is invisible — until someone leaves.'
				: 'Toegangschaos zie je niet — tot er iemand weggaat.' }}
		</h2>

		<div class="grid md:grid-cols-3 gap-5">
			@foreach ([
				['t_nl' => 'Excel, mail, WhatsApp', 't_en' => 'Excel, email, WhatsApp',
				 'd_nl' => 'Wie heeft toegang tot Salesforce? Wie had ook al weer de alarmcode? Kennis zit verspreid in documenten en hoofden.',
				 'd_en' => 'Who has access to Salesforce? Who had the alarm code? Knowledge lives scattered across docs and people\'s heads.'],
				['t_nl' => 'Offboarding vergeten', 't_en' => 'Missed offboarding',
				 'd_nl' => 'Ex-medewerkers houden toegang tot systemen lang nadat ze weg zijn. Security-incident of privacy-risico wachten op gebeuren.',
				 'd_en' => 'Former staff retain access to systems long after leaving. A security or privacy incident waiting to happen.'],
				['t_nl' => 'Geen bewijs', 't_en' => 'No audit trail',
				 'd_nl' => 'Bij een audit of incident kan je niet aantonen wie wat wanneer mocht. Iedereen wijst naar iemand anders.',
				 'd_en' => 'In an audit or incident you cannot prove who had what access when. Everyone points at someone else.'],
			] as $p)
				<div class="card">
					<div class="text-red-600 text-xl mb-2">✕</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $p['t_en'] : $p['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $p['d_en'] : $p['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- FEATURES --}}
<section class="py-16 bg-[color:var(--color-surface)]" id="features">
	<div class="max-w-[1200px] mx-auto px-6">
		<span class="pill pill-teal mb-4">{{ $isEn ? 'What AccessGuard gives you' : 'Wat AccessGuard je geeft' }}</span>
		<h2 class="display-2 mb-10 max-w-3xl">
			{{ $isEn
				? 'One overview. A clear process. Audit-ready.'
				: 'Eén overzicht. Een helder proces. Audit-ready.' }}
		</h2>

		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
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
				['icon' => '→|', 't_nl' => 'Onboarding + offboarding', 't_en' => 'Onboarding + offboarding',
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
				<div class="card h-full">
					<div class="text-2xl mb-3">{{ $f['icon'] }}</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $f['t_en'] : $f['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $f['d_en'] : $f['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- HOW IT WORKS --}}
<section class="py-16 bg-white" id="how">
	<div class="max-w-[1100px] mx-auto px-6">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'How it works' : 'Hoe het werkt' }}</span>
		<h2 class="display-2 mb-10 max-w-3xl">
			{{ $isEn ? 'From chaos to grip in four steps.' : 'Van chaos naar grip in vier stappen.' }}
		</h2>

		<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
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
				<div class="card">
					<div class="text-4xl font-black text-[color:var(--color-accent)] mb-3 leading-none">{{ $s['n'] }}</div>
					<h3 class="font-bold mb-2">{{ $isEn ? $s['t_en'] : $s['t_nl'] }}</h3>
					<p class="text-sm text-[color:var(--color-ink-muted)] leading-relaxed">{{ $isEn ? $s['d_en'] : $s['d_nl'] }}</p>
				</div>
			@endforeach
		</div>
	</div>
</section>

{{-- FOR WHO --}}
<section class="py-16 bg-[color:var(--color-surface)]">
	<div class="max-w-[1100px] mx-auto px-6 grid lg:grid-cols-[1fr_1.2fr] gap-10 items-center">
		<div>
			<span class="pill pill-teal mb-4">{{ $isEn ? 'Who is this for' : 'Voor wie' }}</span>
			<h2 class="display-2 mb-6">
				{{ $isEn ? 'For teams that want control without an IT department.' : 'Voor teams die grip willen houden zonder IT-afdeling.' }}
			</h2>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-4">
				{{ $isEn
					? 'Most access management tools assume a dedicated IAM team. AccessGuard is built for 10–200 person SMBs where the office manager, HR lead or operational director keeps track.'
					: 'De meeste access-management tools gaan uit van een dedicated IAM-team. AccessGuard is gebouwd voor 10–200-persoons MKB waar de office manager, HR-lead of operationeel directeur het bijhoudt.' }}
			</p>
			<p class="text-[color:var(--color-ink-muted)] leading-relaxed">
				{{ $isEn
					? 'You don\'t need to connect every system. Start by writing down what you already know; work step by step toward complete coverage.'
					: 'Je hoeft niet elk systeem te koppelen. Begin met vastleggen wat je nu al weet; werk stap voor stap naar complete dekking.' }}
			</p>
		</div>

		<div>
			<div class="card">
				<h3 class="font-bold mb-4 text-sm uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ $isEn ? 'Typical fit' : 'Typische fit' }}</h3>
				<ul class="space-y-3 text-sm">
					@foreach ([
						['nl' => 'Organisaties van 10–200 medewerkers', 'en' => 'Organisations of 10–200 staff'],
						['nl' => 'Geen dedicated IT-afdeling of IAM-team', 'en' => 'No dedicated IT or IAM team'],
						['nl' => 'Gebruik van 5–30 SaaS-systemen (M365, Slack, Salesforce, Exact, etc.)', 'en' => 'Using 5–30 SaaS systems (M365, Slack, Salesforce, Exact, etc.)'],
						['nl' => 'Compliance-eisen (ISO 27001, NEN 7510, GDPR-audit) die bewijs vragen', 'en' => 'Compliance requirements (ISO 27001, NEN 7510, GDPR audits) that demand proof'],
						['nl' => 'Externe leveranciers + tijdelijke krachten die ook toegang krijgen', 'en' => 'External suppliers + temporary staff that also get access'],
					] as $item)
						<li class="flex items-start gap-3">
							<span class="shrink-0 w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 inline-flex items-center justify-center mt-0.5">
								<svg class="w-3 h-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6l3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
<section class="py-16 bg-white">
	<div class="max-w-[900px] mx-auto px-6 text-center">
		<span class="pill pill-ink mb-4">{{ $isEn ? 'Pricing' : 'Prijzen' }}</span>
		<h2 class="display-2 mb-4">
			{{ $isEn ? 'Included from Pro.' : 'Inbegrepen vanaf Pro.' }}
		</h2>
		<p class="text-[color:var(--color-ink-muted)] leading-relaxed mb-8 max-w-2xl mx-auto">
			{{ $isEn
				? 'AccessGuard is included in the Pro plan (€12/month) with a 14-day free trial. Business adds multi-user access and higher limits. Free plan lets you preview, but managing access requires Pro.'
				: 'AccessGuard is inbegrepen vanaf het Pro-plan (€12/maand) met 14 dagen gratis proef. Business voegt multi-user toegang en hogere limieten toe. Het gratis plan laat je kijken, maar beheer vereist Pro.' }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			<a href="{{ route('pricing', ['locale' => $locale]) }}" class="btn-accent">
				{{ $isEn ? 'See all pricing' : 'Bekijk alle prijzen' }}
			</a>
			@if (! $isAuthed)
				<a href="{{ route('register', ['locale' => $locale]) }}" class="btn-dark">
					{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
				</a>
			@endif
		</div>
	</div>
</section>

{{-- FINAL CTA --}}
<section class="section-dark py-16">
	<div class="max-w-[900px] mx-auto px-6 text-center">
		<h2 class="display-2 mb-5 text-white">
			{{ $isEn ? 'Stop guessing. Start knowing.' : 'Stop met gissen. Begin met weten.' }}
		</h2>
		<p class="text-[color:var(--color-on-dark-muted)] leading-relaxed mb-8 max-w-2xl mx-auto">
			{{ $isEn
				? 'In a quarter of an hour you have your first matrix filled in. The rest follows naturally — review cycles, offboarding, evidence and proof.'
				: 'In een kwartier heb je je eerste matrix ingevuld. De rest volgt vanzelf — review-cycli, offboarding, bewijs en audit-spoor.' }}
		</p>
		<div class="flex flex-wrap gap-3 justify-center">
			@if ($hasAccess)
				<a href="{{ route('tools.accessguard.index', ['locale' => $locale]) }}" class="btn-accent">
					{{ $isEn ? 'Open AccessGuard' : 'Open AccessGuard' }}
				</a>
			@elseif ($isAuthed)
				<a href="{{ route('pricing', ['locale' => $locale]) }}" class="btn-accent">
					{{ $isEn ? 'Upgrade to Pro' : 'Upgrade naar Pro' }}
				</a>
			@else
				<a href="{{ route('register', ['locale' => $locale]) }}" class="btn-accent">
					{{ $isEn ? 'Start free trial' : 'Start gratis proef' }}
				</a>
				<a href="/{{ $locale }}/contact?topic=accessguard" class="btn-ghost-light">
					{{ $isEn ? 'Talk to us' : 'Plan een gesprek' }}
				</a>
			@endif
		</div>
	</div>
</section>

@endsection
