<?php

namespace App\Services\QualityScan\Data;

/**
 * Het gedestilleerde, compacte pakket dat we uit een pagina halen — de basis
 * voor zowel de deterministische checks als de AI-analyse. toArray() levert de
 * JSON-representatie die naar Claude gaat; hash() identificeert ongewijzigde input.
 */
final class ExtractedContent
{
	public function __construct(
		public readonly ?string $title,
		public readonly ?string $metaDescription,
		public readonly ?string $metaRobots,
		public readonly ?string $canonical,
		public readonly array $ogTags,          // ['title'=>?, 'description'=>?, 'type'=>?]
		public readonly ?string $generator,
		public readonly ?string $lang,
		public readonly array $headings,        // [['level'=>1,'text'=>'...'], ...]
		public readonly array $images,          // [['src'=>'...','alt'=>'...'], ...]
		public readonly array $links,           // [['href'=>'...','anchor'=>'...'], ...]
		public readonly string $bodyText,
		public readonly ?string $cookieText,
		public readonly string $finalUrl,
		public readonly bool $isHttps,
	) {
	}

	public function toArray(): array
	{
		return [
			'url'              => $this->finalUrl,
			'title'            => $this->title,
			'meta_description' => $this->metaDescription,
			'meta_robots'      => $this->metaRobots,
			'canonical'        => $this->canonical,
			'og_tags'          => $this->ogTags,
			'generator'        => $this->generator,
			'lang'             => $this->lang,
			'headings'         => $this->headings,
			'images'           => $this->images,
			'links'            => $this->links,
			'body_text'        => $this->bodyText,
			'cookie_text'      => $this->cookieText,
		];
	}

	/** SHA-256 van de gedestilleerde input — ongewijzigde pagina's hebben dezelfde hash. */
	public function hash(): string
	{
		return hash('sha256', json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
}
