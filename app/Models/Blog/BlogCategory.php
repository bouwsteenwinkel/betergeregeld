<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
	protected $table = 'blog_categories';

	protected $fillable = ['slug', 'name', 'pillar_title', 'intro', 'sort_order'];

	public function posts(): HasMany
	{
		return $this->hasMany(BlogPost::class, 'category_id')->orderByDesc('published_at');
	}

	public function pillarPost(): ?BlogPost
	{
		return $this->posts()->where('is_pillar', true)->first();
	}
}
