@php /** @var \App\Support\ChannelSite $site */ @endphp
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
			<a href="{{ $site->url() }}">Home</a>
			<a href="{{ $site->url('plaatsen') }}">Plaatsen</a>
			<a href="{{ $site->url('blog') }}">Blog</a>
			<a href="{{ $site->url('over-ons') }}">Over ons</a>
		</nav>
		<a href="{{ $site->url() }}#contact" class="btn">Gratis voorbeeld</a>
	</div>
</header>
