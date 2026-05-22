@php $locale = app()->getLocale(); @endphp
<div class="border border-[color:var(--color-line)] rounded p-3">
	<div class="flex items-start gap-3">
		<span @class([
			'inline-block w-2 h-2 mt-1.5 rounded-full shrink-0',
			'bg-red-500' => $f->severity === 'critical',
			'bg-orange-500' => $f->severity === 'high',
			'bg-amber-500' => $f->severity === 'medium',
			'bg-slate-400' => $f->severity === 'low',
		])></span>
		<div class="flex-1 min-w-0">
			<div class="flex items-center gap-2 flex-wrap">
				<span class="font-semibold">{{ $f->title }}</span>
				<span class="text-xs px-2 py-0.5 rounded border border-[color:var(--color-line)] text-[color:var(--color-ink-muted)] uppercase">{{ $f->check_type }}</span>
				<span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-700 uppercase">{{ $f->severity }}</span>
			</div>
			@if ($f->detail)
				<div class="text-sm text-[color:var(--color-ink-muted)] mt-1 whitespace-pre-line">{{ $f->detail }}</div>
			@endif
			@if ($f->resolution_notes)
				<div class="text-xs text-[color:var(--color-ink-muted)] mt-2 italic">{{ __('Notitie') }}: {{ $f->resolution_notes }}</div>
			@endif
			<div class="text-xs text-[color:var(--color-ink-muted)] mt-2">
				{{ __('Eerst gezien') }}: {{ $f->first_detected_at?->diffForHumans() }}
				·
				{{ __('Laatst') }}: {{ $f->last_detected_at?->diffForHumans() }}
			</div>

			<form method="POST" action="{{ route('tools.radar.findings.update-status', ['locale' => $locale, 'id' => $f->id]) }}" class="mt-3 flex flex-wrap items-center gap-2">
				@csrf
				<select name="status" class="text-xs px-2 py-1 rounded border border-[color:var(--color-line)] bg-white">
					<option value="confirmed" @selected($f->status === 'confirmed')>{{ __('Bevestigd') }}</option>
					<option value="in_progress" @selected($f->status === 'in_progress')>{{ __('In behandeling') }}</option>
					<option value="planned" @selected($f->status === 'planned')>{{ __('Gepland') }}</option>
					<option value="accepted_risk" @selected($f->status === 'accepted_risk')>{{ __('Accepteer risico') }}</option>
					<option value="resolved" @selected($f->status === 'resolved')>{{ __('Opgelost') }}</option>
					<option value="ignored" @selected($f->status === 'ignored')>{{ __('Negeer') }}</option>
					<option value="false_positive" @selected($f->status === 'false_positive')>{{ __('False positive') }}</option>
					@if (in_array($f->status, ['resolved','ignored','false_positive','accepted_risk'], true))
						<option value="new">{{ __('Heropen') }}</option>
					@endif
				</select>
				<input type="text" name="notes" maxlength="200" placeholder="{{ __('Notitie (optioneel)') }}" class="text-xs px-2 py-1 rounded border border-[color:var(--color-line)] bg-white flex-1 min-w-[160px]">
				<button type="submit" class="text-xs px-3 py-1 rounded bg-[color:var(--color-ink)] text-white hover:opacity-90">{{ __('Update') }}</button>
			</form>
		</div>
	</div>
</div>
