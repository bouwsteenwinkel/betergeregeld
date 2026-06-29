@php /** @var \App\Support\ChannelSite $site */ $cta = $site->navCta(); @endphp
<header class="nav">
	<div class="wrap nav-inner">
		<a href="{{ $site->url() }}" class="logo">
			@if ($site->logoImage())
				<img src="{{ $site->logoImage() }}" alt="{{ $site->name() }}" style="height:34px;display:block">
			@else
				{!! $site->brand('logo_text', $site->name()) !!}
			@endif
		</a>
		<nav class="nav-links">
			@foreach ($site->navMenu() as $item)
				<a href="{{ $site->navHref($item['href'] ?? '') }}">{{ $item['label'] ?? '' }}</a>
			@endforeach
		</nav>
		<a href="{{ $site->navHref($cta['href']) }}" class="btn">{{ $cta['label'] }}</a>
	</div>
</header>
