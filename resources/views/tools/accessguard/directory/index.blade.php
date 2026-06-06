@extends('layouts.app')

@section('title', __('Directory') . ', AccessGuard')

@php
	$locale = app()->getLocale();
	$crumb = __('Directory sync');
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1400px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-900">{{ session('error') }}</div>
		@endif

		<div class="card">
			<div class="flex items-start justify-between gap-4 flex-wrap">
				<div>
					<h2 class="text-lg font-bold mb-1">{{ __('Microsoft 365 / Entra ID') }}</h2>
					<p class="text-sm text-[color:var(--color-ink-muted)]">
						{{ __('Haal je users automatisch binnen uit Microsoft Entra ID. AccessGuard maakt of koppelt Personen op basis van externe ID en e-mail. accountEnabled=false wordt status=inactive.') }}
					</p>
				</div>
				@if ($connection && $connection->status === 'connected')
					<span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold">{{ __('Verbonden') }}</span>
				@elseif ($connection && $connection->status === 'error')
					<span class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">{{ __('Fout') }}</span>
				@else
					<span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">{{ __('Niet verbonden') }}</span>
				@endif
			</div>

			@if (! $configured)
				<div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded text-sm text-amber-900">
					<strong>{{ __('Nog niet klaar op server') }}:</strong>
					{{ __('vraag je admin om MICROSOFT_CLIENT_ID, MICROSOFT_CLIENT_SECRET en MICROSOFT_REDIRECT_URI in .env te zetten en een app-registratie te maken in Azure Portal. Zie DEPLOY.md §13.') }}
				</div>
			@elseif ($connection)
				<dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 mt-4 text-sm">
					<div>
						<dt class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Tenant') }}</dt>
						<dd class="font-mono">{{ $connection->display_name ?? $connection->external_tenant_id ?? ', ' }}</dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Laatste sync') }}</dt>
						<dd>{{ $connection->last_synced_at?->diffForHumans() ?? __('nog niet') }}</dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Token verloopt') }}</dt>
						<dd>{{ $connection->expires_at?->diffForHumans() ?? ', ' }}</dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Users in laatste sync') }}</dt>
						<dd>{{ $connection->last_sync_users_seen }}
							@if ($connection->last_sync_users_created > 0 || $connection->last_sync_users_updated > 0)
								<span class="text-xs text-[color:var(--color-ink-muted)]">
									({{ $connection->last_sync_users_created }} {{ __('nieuw') }},
									{{ $connection->last_sync_users_updated }} {{ __('bijgewerkt') }})
								</span>
							@endif
						</dd>
					</div>
					<div>
						<dt class="text-xs uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Groups in laatste sync') }}</dt>
						<dd>{{ $connection->last_sync_groups_seen ?? 0 }}
							@if (($connection->last_sync_memberships_seen ?? 0) > 0)
								<span class="text-xs text-[color:var(--color-ink-muted)]">
									({{ $connection->last_sync_memberships_seen }} {{ __('lidmaatschappen') }})
								</span>
							@endif
						</dd>
					</div>
				</dl>

				@if ($connection->last_sync_message)
					<p class="mt-3 text-sm text-red-700 font-mono bg-red-50 border border-red-200 rounded p-2">
						{{ $connection->last_sync_message }}
					</p>
				@endif

				<div class="mt-5 flex gap-2 flex-wrap">
					<form method="POST" action="{{ route('tools.accessguard.directory.sync', ['locale' => $locale]) }}">
						@csrf
						<button type="submit" class="btn-dark text-sm">{{ __('Nu synchroniseren') }}</button>
					</form>
					<a href="{{ route('tools.accessguard.directory.connect', ['locale' => $locale]) }}" class="btn-light text-sm">
						{{ __('Opnieuw verbinden') }}
					</a>
					<form method="POST" action="{{ route('tools.accessguard.directory.disconnect', ['locale' => $locale]) }}"
						onsubmit="return confirm('{{ __('Verbinding verwijderen? Gesyncte personen blijven staan.') }}');">
						@csrf
						<button type="submit" class="text-sm px-3 py-1.5 text-red-700 hover:bg-red-50 rounded">
							{{ __('Verbinding verwijderen') }}
						</button>
					</form>
				</div>
			@else
				<div class="mt-4">
					<a href="{{ route('tools.accessguard.directory.connect', ['locale' => $locale]) }}" class="btn-dark text-sm">
						{{ __('Verbind met Microsoft 365') }}
					</a>
					<p class="text-xs text-[color:var(--color-ink-muted)] mt-2">
						{{ __('Je wordt doorgestuurd naar Microsoft om toestemming te geven (User.Read.All + Directory.Read.All).') }}
					</p>
				</div>
			@endif
		</div>

		<div class="card">
			<h3 class="text-sm font-bold mb-2 uppercase tracking-wider text-[color:var(--color-ink-muted)]">{{ __('Wat sync doet') }}</h3>
			<ul class="text-sm space-y-2 list-disc pl-5">
				<li>{{ __('Pullt alle users uit Entra ID (paginated).') }}</li>
				<li>{{ __('Matcht op external_id → e-mail → anders nieuwe Persoon aanmaken.') }}</li>
				<li>{{ __('Schrijft first/last name, job_title, department, last_sign_in_at.') }}</li>
				<li>{{ __('accountEnabled=false in Entra → status=inactive in AccessGuard.') }}</li>
				<li>{{ __('Scanner pakt users die 90+ dagen niet ingelogd zijn als risico (severity 3, 180+ dagen = 4).') }}</li>
				<li>{{ __('Pullt security groups als AccessProfiles; leden-sets worden bij elke sync opnieuw geprunet.') }}</li>
				<li>{{ __('Op het Profielen-overzicht kun je een profile in één klik toepassen op alle leden.') }}</li>
				<li>{{ __('Dagelijkse auto-sync loopt via accessguard:sync-directories.') }}</li>
			</ul>
		</div>
	</div>
</section>

@endsection
