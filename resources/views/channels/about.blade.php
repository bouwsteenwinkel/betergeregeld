@php /** @var \App\Support\ChannelSite $site */ $a = $site->get('about', []); @endphp
@extends('channels.layout')

@section('title', ($a['title'] ?? 'Over ons') . ' — ' . $site->name())
@section('description', $a['lead'] ?? $site->homeDescription())

@section('content')
	<section class="hero">
		<div class="wrap">
			<span class="kicker"><span class="kicker-line"></span> Over ons</span>
			<h1>{{ $a['title'] ?? 'Over ' . $site->name() }}</h1>
			@if (!empty($a['lead']))<p class="lead">{{ $a['lead'] }}</p>@endif
		</div>
	</section>

	<section>
		<div class="wrap" style="max-width:720px">
			<div class="prose">
				@foreach (($a['body'] ?? []) as $p)
					<p>{{ $p }}</p>
				@endforeach
			</div>

			@if (!empty($a['stats']))
				<div class="grid cols-3" style="margin-top:2rem">
					@foreach ($a['stats'] as $s)
						<div class="card" style="text-align:center">
							<div style="font-size:1.8rem;font-weight:800;color:var(--c-primary)">{{ $s['value'] }}</div>
							<div class="muted">{{ $s['label'] }}</div>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</section>

	@if (!empty($a['team']))
		<section style="background:var(--c-surface)">
			<div class="wrap">
				<span class="kicker"><span class="kicker-line"></span> Het team</span>
				<h2>De mensen achter {{ $site->name() }}</h2>
				<p class="muted section-lead">Een klein, vast team dat jouw vak snapt, van ontwerp tot vindbaarheid.</p>
				<div class="team-grid">
					@foreach ($a['team'] as $i => $m)
						<div class="team-card">
							<div class="team-avatar">
								@php $tImg = ! empty($m['image']) ? $site->image($m['image']) : null; @endphp
								@if ($tImg)
									<img src="{{ $tImg }}" srcset="{{ $site->imageSrcset($m['image']) }}" sizes="120px" alt="{{ $m['name'] }}, {{ $m['role'] }}" loading="lazy" width="120" height="120">
								@else
									@include('channels.partials.avatar', ['av' => array_merge($m['avatar'] ?? [], ['id' => 'tm' . $i, 'alt' => $m['name']])])
								@endif
							</div>
							<h3>{{ $m['name'] }}</h3>
							<span class="team-role">{{ $m['role'] }}</span>
							@if (!empty($m['bio']))<p class="muted">{{ $m['bio'] }}</p>@endif
						</div>
					@endforeach
				</div>
			</div>
		</section>
	@endif

	@include('channels.partials.lead-form')
@endsection
