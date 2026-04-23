<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogTag extends Model
{
	protected $table = 'blog_tags';

	protected $fillable = ['slug', 'name'];

	public function posts(): BelongsToMany
	{
		return $this->belongsToMany(BlogPost::class, 'blog_post_tag', 'tag_id', 'post_id');
	}
}
