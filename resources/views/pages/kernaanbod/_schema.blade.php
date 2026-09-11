{{-- JSON-LD voor een kernaanbod-pagina: Service, kruimelpad en de veelgestelde vragen.
     Verwacht $p (zie _inhoud.blade.php) en $schemaNaam. --}}
@php
	$__kern_url = url('/nl/' . $p['slug']);
	$__kern_blokken = [
		[
			"\x40context"   => 'https://schema.org',
			"\x40type"      => 'Service',
			'name'        => $schemaNaam,
			'description' => $p['lead'],
			'serviceType' => $schemaNaam,
			'provider'    => [
				"\x40type" => 'Organization',
				"\x40id"   => url('/#organization'),
				'name'  => 'Beter Geregeld ICT',
				'url'   => url('/'),
			],
			'areaServed'  => ["\x40type" => 'Country', 'name' => 'Netherlands'],
			'inLanguage'  => 'nl',
			'url'         => $__kern_url,
		],
		[
			"\x40context"        => 'https://schema.org',
			"\x40type"           => 'BreadcrumbList',
			'itemListElement' => [
				["\x40type" => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => url('/nl')],
				["\x40type" => 'ListItem', 'position' => 2, 'name' => 'Diensten', 'item' => url('/nl/diensten')],
				["\x40type" => 'ListItem', 'position' => 3, 'name' => $p['kruimel'], 'item' => $__kern_url],
			],
		],
		[
			"\x40context"  => 'https://schema.org',
			"\x40type"     => 'FAQPage',
			'mainEntity' => array_map(fn ($q) => [
				"\x40type"         => 'Question',
				'name'           => $q['v'],
				'acceptedAnswer' => ["\x40type" => 'Answer', 'text' => $q['a']],
			], $p['faq']),
		],
	];
@endphp
@foreach ($__kern_blokken as $__kern_blok)
	<script type="application/ld+json">
	{!! json_encode($__kern_blok, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
	</script>
@endforeach
