<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class BlogPost extends Model
{

	/**
	 * De talen waarin de blog bestaat.
	 *
	 * Bewust los van SetLocale::SUPPORTED (nl + en): dat zijn twee verschillende
	 * vragen — welke talen bedient de SITE, en in welke talen schrijven we. De
	 * Engelse blog is per 18-08-2026 uitgefaseerd. Het waren 778 vertalingen van
	 * Nederlandse artikelen die op NEDERLANDSE zoektermen met hun eigen origineel
	 * concurreerden; gemeten over 90 dagen stond /en/blog/factuur-in-nederlands-en-engels
	 * op positie 10 en /nl/… op 17 voor dezelfde term. Samen 86 clicks op 14.492
	 * vertoningen, waarvan maar 43% uit Nederland kwam.
	 *
	 * De rijen blijven in de database staan: alleen serveren, adverteren (hreflang,
	 * sitemap) en bijmaken stopt. Terugdraaien is deze constante weer uitbreiden.
	 *
	 * @var array<int,string>
	 */
	public const LOCALES = ['nl'];

	protected $table = 'blog_posts';

	protected $fillable = [
		'category_id', 'locale', 'translation_of_post_id', 'channel',
		'slug', 'title', 'meta_title', 'excerpt', 'body', 'image',
		'reading_time_min', 'is_pillar', 'featured', 'published_at',
		'ai_generated', 'ai_model', 'ai_prompt_meta',
	];

	protected function casts(): array
	{
		return [
			'is_pillar' => 'boolean',
			'featured' => 'boolean',
			'published_at' => 'datetime',
			'ai_generated' => 'boolean',
			'ai_prompt_meta' => 'array',
		];
	}

	/** Posts van de hoofd-site (channel = null) of van een specifiek kanaal. */
	public function scopeForChannel(Builder $q, ?string $channel): Builder
	{
		return $channel === null
			? $q->whereNull('channel')
			: $q->where('channel', $channel);
	}

	public function category(): BelongsTo
	{
		return $this->belongsTo(BlogCategory::class, 'category_id');
	}

	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'post_id', 'tag_id');
	}

	public function scopePublished(Builder $q): Builder
	{
		return $q->whereNotNull('published_at')->where('published_at', '<=', now());
	}

	/**
	 * Related posts based on tag overlap within the same category first,
	 * then anywhere else. Produces exactly $limit suggestions — the URL
	 * block at the bottom of every post page runs on this.
	 */
	public function relatedPosts(int $limit = 5): \Illuminate\Support\Collection
	{
		$tagIds = $this->tags()->pluck('blog_tags.id');
		$locale = $this->locale ?: 'nl';

		$scored = static::query()
			->published()
			->where('locale', $locale)
			->where('id', '!=', $this->id)
			->with('category')
			->when($tagIds->isNotEmpty(), fn ($q) => $q->whereHas('tags', fn ($tq) => $tq->whereIn('blog_tags.id', $tagIds)))
			->orderByRaw('category_id = ? DESC', [$this->category_id])
			->orderByDesc('is_pillar')
			->orderByDesc('published_at')
			->limit($limit)
			->get();

		if ($scored->count() >= $limit) return $scored;

		// Fall back to same-category in same locale, then newest anywhere
		// in this locale, to always hit $limit.
		$seenIds = $scored->pluck('id')->push($this->id)->all();
		$fill = static::query()
			->published()
			->where('locale', $locale)
			->whereNotIn('id', $seenIds)
			->where('category_id', $this->category_id)
			->orderByDesc('is_pillar')
			->orderByDesc('published_at')
			->limit($limit - $scored->count())
			->get();

		return $scored->concat($fill);
	}
}
