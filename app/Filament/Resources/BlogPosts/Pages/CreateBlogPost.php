<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
	protected static string $resource = BlogPostResource::class;

	protected function mutateFormDataBeforeCreate(array $data): array
	{
		return self::autoReadingTime($data);
	}

	public static function autoReadingTime(array $data): array
	{
		if (empty($data['reading_time_min']) && ! empty($data['body'])) {
			$words = max(1, str_word_count(strip_tags($data['body'])));
			$data['reading_time_min'] = max(1, (int) ceil($words / 200));
		}
		return $data;
	}
}
