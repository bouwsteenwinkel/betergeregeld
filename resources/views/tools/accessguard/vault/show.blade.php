@extends('layouts.app')

@section('title', $cred->name . ' — Vault')

@php
	$locale = app()->getLocale();
	$crumb = __('Vault') . ' / ' . $cred->name;
	$typeLabels = [
		'password' => __('Wachtwoord'), 'token' => __('Token'), 'api_key' => __('API key'),
		'ssh_key' => __('SSH key'), 'cert' => __('Certificaat'), 'other' => __('Overig'),
	];
	$actionLabels = [
		'created' => __('aangemaakt'), 'updated' => __('bijgewerkt'), 'viewed' => __('bekeken'),
		'decrypted' => __('gedecrypteerd'), 'rotated' => __('geroteerd'), 'deleted' => __('verwijderd'),
		'acl_granted' => __('ACL verleend'), 'acl_revoked' => __('ACL ingetrokken'),
	];
@endphp

@section('content')

@include('tools.accessguard._header', ['crumb' => $crumb])
@include('tools.accessguard._subnav')

<section class="py-6">
	<div class="max-w-[1100px] mx-auto px-6 space-y-4">
		@if (session('status'))
			<div class="card text-sm bg-emerald-50 border-emerald-200 text-emerald-900">{{ session('status') }}</div>
		@endif
		@if (session('error'))
			<div class="card text-sm bg-red-50 border-red-200 text-red-800">{{ session('error') }}</div>
		@endif

		<div class="card">
			<div class="flex items-start justify-between gap-4 flex-wrap">
				<div class="flex-1 min-w-0">
					<h2 class="text-xl font-bold">{{ $cred->name }}</h2>
					<div class="text-xs text-[color:var(--color-ink-muted)] mt-1 flex items-center gap-2 flex-wrap">
						<span>{{ $typeLabels[$cred->type] ?? $cred->type }}</span>
						@if ($cred->system) · <span>{{ $cred->system->name }}</span> @endif
						@if ($cred->accessItem) · <span>{{ $cred->accessItem->name }}</span> @endif
						@if ($cred->username) · <span class="font-mono">{{ $cred->username }}</span> @endif
						@if ($cred->isExpired())
							<span class="inline-flex items-center px-2 py-0.5 rounded bg-red-100 text-red-800 font-semibold">{{ __('Verlopen') }}</span>
						@elseif ($cred->isRotationDue())
							<span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold">{{ __('Rotatie nodig') }}</span>
						@endif
					</div>
				</div>
				<div class="flex items-center gap-2">
					@if ($canAdmin)
						<a href="{{ route('tools.accessguard.vault.edit', ['locale' => $locale, 'id' => $cred->id]) }}" class="text-sm py-2 px-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]">{{ __('Bewerken') }}</a>
						<form method="POST" action="{{ route('tools.accessguard.vault.destroy', ['locale' => $locale, 'id' => $cred->id]) }}" class="inline" onsubmit="return confirm('{{ __('Credential definitief verwijderen?') }}');">
							@csrf
							@method('DELETE')
							<button type="submit" class="text-sm py-2 px-3 rounded border border-red-300 text-red-700 hover:bg-red-50">{{ __('Verwijderen') }}</button>
						</form>
					@endif
				</div>
			</div>
		</div>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Secret') }}</h3>
			@if ($canDecrypt)
				<div class="flex items-center gap-3">
					<code id="secret-display" class="flex-1 font-mono text-sm bg-slate-100 px-3 py-2 rounded border border-[color:var(--color-line)] select-all">{{ str_repeat('•', 32) }}</code>
					<button type="button" id="btn-reveal" class="btn-accent text-sm">{{ __('Toon') }}</button>
					<button type="button" id="btn-copy" class="text-sm py-2 px-3 rounded border border-[color:var(--color-line)] hover:bg-[color:var(--color-surface-soft,#fafafa)]" disabled>{{ __('Kopieer') }}</button>
				</div>
				<p class="text-xs text-[color:var(--color-ink-soft)] mt-2" id="reveal-hint">{{ __('Wordt 30 seconden getoond, daarna automatisch verborgen. Elke decrypt wordt gelogd.') }}</p>
			@else
				<p class="text-sm text-[color:var(--color-ink-muted)]">{{ __('Je hebt alleen view-rechten. Vraag een admin om decrypt.') }}</p>
			@endif
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
			<div class="card">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Toegang') }}</h3>
				<div class="text-sm mb-3">
					<strong>{{ __('Eigenaar:') }}</strong> <span class="font-mono">{{ $userEmails[$cred->created_by_user_id] ?? $cred->created_by_user_id }}</span>
					<span class="text-xs text-[color:var(--color-ink-muted)]">({{ __('view + decrypt + admin') }})</span>
				</div>
				@if ($acl->isEmpty())
					<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Geen extra gebruikers toegelaten.') }}</p>
				@else
					<table class="w-full text-sm">
						<tbody>
							@foreach ($acl as $a)
								<tr class="border-b border-[color:var(--color-line)]/60">
									<td class="py-1.5 font-mono text-xs">{{ $userEmails[$a->user_id] ?? $a->user_id }}</td>
									<td class="py-1.5 text-xs text-[color:var(--color-ink-muted)]">
										@if ($a->revoked_at)
											<span class="text-slate-400">{{ __('ingetrokken :date', ['date' => $a->revoked_at->format('d-m-Y')]) }}</span>
										@else
											@if ($a->can_view)<span class="inline-block px-1.5 py-0.5 rounded bg-slate-100">V</span>@endif
											@if ($a->can_decrypt)<span class="inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">D</span>@endif
											@if ($a->can_rotate)<span class="inline-block px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">R</span>@endif
											@if ($a->can_admin)<span class="inline-block px-1.5 py-0.5 rounded bg-red-100 text-red-800">A</span>@endif
										@endif
									</td>
									<td class="py-1.5 text-right">
										@if ($canAdmin && $a->isActive())
											<form method="POST" action="{{ route('tools.accessguard.vault.revoke-acl', ['locale' => $locale, 'id' => $cred->id, 'aclId' => $a->id]) }}" class="inline" onsubmit="return confirm('{{ __('Toegang intrekken?') }}');">
												@csrf
												<button type="submit" class="text-xs text-red-600 hover:underline">{{ __('intrekken') }}</button>
											</form>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				@endif

				@if ($canAdmin)
					<form method="POST" action="{{ route('tools.accessguard.vault.grant-acl', ['locale' => $locale, 'id' => $cred->id]) }}" class="mt-4 pt-4 border-t border-[color:var(--color-line)] space-y-2">
						@csrf
						<div class="flex items-center gap-2">
							<input type="email" name="user_email" required placeholder="collega@demo.bv" class="field-input py-1 text-sm flex-1">
							<button type="submit" class="text-xs px-3 py-1 rounded bg-slate-800 text-white hover:bg-slate-700">{{ __('+ Verleen toegang') }}</button>
						</div>
						<div class="flex flex-wrap items-center gap-3 text-xs">
							<label class="flex items-center gap-1"><input type="checkbox" name="can_view" value="1" checked> {{ __('view') }}</label>
							<label class="flex items-center gap-1"><input type="checkbox" name="can_decrypt" value="1"> {{ __('decrypt') }}</label>
							<label class="flex items-center gap-1"><input type="checkbox" name="can_rotate" value="1"> {{ __('rotate') }}</label>
							<label class="flex items-center gap-1"><input type="checkbox" name="can_admin" value="1"> {{ __('admin') }}</label>
						</div>
					</form>
				@endif
			</div>

			<div class="card">
				<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Metadata') }}</h3>
				<dl class="text-sm space-y-1">
					<div class="flex justify-between">
						<dt class="text-[color:var(--color-ink-muted)]">{{ __('Aangemaakt') }}</dt>
						<dd>{{ $cred->created_at->format('d-m-Y H:i') }}</dd>
					</div>
					@if ($cred->last_rotated_at)
						<div class="flex justify-between">
							<dt class="text-[color:var(--color-ink-muted)]">{{ __('Laatst geroteerd') }}</dt>
							<dd>{{ $cred->last_rotated_at->format('d-m-Y H:i') }}</dd>
						</div>
					@endif
					@if ($cred->rotation_interval_days)
						<div class="flex justify-between">
							<dt class="text-[color:var(--color-ink-muted)]">{{ __('Rotatie-interval') }}</dt>
							<dd>{{ $cred->rotation_interval_days }} {{ __('dagen') }}</dd>
						</div>
					@endif
					@if ($cred->expires_at)
						<div class="flex justify-between">
							<dt class="text-[color:var(--color-ink-muted)]">{{ __('Verloopt') }}</dt>
							<dd>{{ $cred->expires_at->format('d-m-Y') }}</dd>
						</div>
					@endif
				</dl>
			</div>
		</div>

		<div class="card">
			<h3 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-ink-muted)] mb-3">{{ __('Access log') }} <span class="text-xs font-normal text-[color:var(--color-ink-soft)]">({{ __('laatste 50') }})</span></h3>
			@if ($logs->isEmpty())
				<p class="text-xs text-[color:var(--color-ink-muted)]">{{ __('Nog geen activiteit gelogd.') }}</p>
			@else
				<div class="text-xs space-y-1">
					@foreach ($logs as $log)
						<div class="flex gap-3 py-1 border-b border-[color:var(--color-line)]/40">
							<span class="text-[color:var(--color-ink-soft)] tabular-nums whitespace-nowrap w-28">{{ $log->occurred_at->format('d-m H:i:s') }}</span>
							<span class="font-mono whitespace-nowrap w-28">{{ $actionLabels[$log->action] ?? $log->action }}</span>
							<span class="text-[color:var(--color-ink-muted)] truncate">{{ $userEmails[$log->user_id] ?? $log->user_id }}</span>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</div>
</section>

@push('scripts')
<script>
(function () {
	const revealBtn = document.getElementById('btn-reveal');
	const copyBtn = document.getElementById('btn-copy');
	const display = document.getElementById('secret-display');
	const hint = document.getElementById('reveal-hint');
	if (!revealBtn) return;

	const decryptUrl = @json(route('tools.accessguard.vault.decrypt', ['locale' => $locale, 'id' => $cred->id]));
	const csrf = document.querySelector('meta[name="csrf-token"]').content;
	let hideTimer = null;
	let currentSecret = null;

	function hide() {
		display.textContent = '•'.repeat(32);
		currentSecret = null;
		copyBtn.disabled = true;
		revealBtn.textContent = @json(__('Toon'));
		revealBtn.disabled = false;
		hint.textContent = @json(__('Wordt 30 seconden getoond, daarna automatisch verborgen. Elke decrypt wordt gelogd.'));
	}

	revealBtn.addEventListener('click', async () => {
		if (currentSecret) { hide(); clearTimeout(hideTimer); return; }
		revealBtn.disabled = true;
		try {
			const resp = await fetch(decryptUrl, {
				method: 'POST',
				headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
			});
			const body = await resp.json();
			if (!resp.ok || !body.ok) {
				hint.textContent = body.error || 'decrypt failed';
				revealBtn.disabled = false;
				return;
			}
			currentSecret = body.secret;
			display.textContent = currentSecret;
			copyBtn.disabled = false;
			revealBtn.textContent = @json(__('Verberg'));
			revealBtn.disabled = false;
			let remaining = 30;
			hint.textContent = @json(__('Nog :n seconden zichtbaar.')).replace(':n', remaining);
			hideTimer = setInterval(() => {
				remaining--;
				if (remaining <= 0) { clearInterval(hideTimer); hide(); return; }
				hint.textContent = @json(__('Nog :n seconden zichtbaar.')).replace(':n', remaining);
			}, 1000);
		} catch (e) {
			hint.textContent = 'decrypt failed';
			revealBtn.disabled = false;
		}
	});

	copyBtn.addEventListener('click', async () => {
		if (!currentSecret) return;
		try {
			await navigator.clipboard.writeText(currentSecret);
			copyBtn.textContent = @json(__('Gekopieerd'));
			setTimeout(() => copyBtn.textContent = @json(__('Kopieer')), 2000);
		} catch (e) {
			copyBtn.textContent = 'clipboard denied';
		}
	});
})();
</script>
@endpush

@endsection
