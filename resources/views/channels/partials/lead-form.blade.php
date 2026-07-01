@php /** @var \App\Support\ChannelSite $site */ @endphp
<section id="contact" style="background:color-mix(in srgb,var(--c-accent) 8%,transparent)">
	<div class="wrap" style="display:grid;gap:2rem">
		<div style="max-width:48ch">
			<span class="kicker"><span class="kicker-line"></span> Gratis &amp; vrijblijvend</span>
			<h2>Vraag een gratis voorbeeld aan</h2>
			<p class="muted">Laat je gegevens achter. We zetten een voorbeeld voor je klaar en nemen binnen 2 werkdagen contact op, zonder verplichtingen.</p>
		</div>

		<div class="card" style="max-width:640px">
			@if ($errors->any())
				<div class="errors">
					Controleer even: {{ implode(' ', $errors->all()) }}
				</div>
			@endif

			<form method="POST" action="{{ $site->url('contact') }}">
				@csrf
				<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
					<input type="hidden" name="facet" value="{{ $facet ?? '' }}">{{-- Groeidiamant-fase --}}

				<div class="grid cols-2">
					<div class="field">
						<label for="contact_name">Naam *</label>
						<input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required>
					</div>
					<div class="field">
						<label for="company">Bedrijf</label>
						<input id="company" name="company" value="{{ old('company') }}">
					</div>
				</div>
				<div class="grid cols-2">
					<div class="field">
						<label for="email">E-mail *</label>
						<input id="email" type="email" name="email" value="{{ old('email') }}" required>
					</div>
					<div class="field">
						<label for="phone">Telefoon *</label>
						<input id="phone" name="phone" value="{{ old('phone') }}" required>
					</div>
				</div>
				<div class="field">
					<label for="city">Plaats</label>
					<input id="city" name="city" value="{{ old('city', $placePrefill ?? '') }}">
				</div>
				<div class="field">
					<label for="message">Vertel kort over je zaak (optioneel)</label>
					<textarea id="message" name="message" rows="3">{{ old('message') }}</textarea>
				</div>
				<button type="submit" class="btn" style="width:100%">Gratis voorbeeld aanvragen</button>
			</form>
		</div>
	</div>
</section>
