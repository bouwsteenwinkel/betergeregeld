<?php

namespace App\Console\Commands;

use App\Models\Blog\BlogPost;
use App\Services\Blog\BlogTranslator;
use Illuminate\Console\Command;
use Throwable;

/**
 * Backfill alle bestaande NL-blog-posts naar opgegeven target-locales.
 * Idempotent: bestaande vertalingen worden overgeslagen (BlogTranslator
 * detecteert (source, locale) en returnt de bestaande rij).
 *
 * Gebruik:
 *   php artisan blog:backfill-translations en de fr es
 *   php artisan blog:backfill-translations en --limit=10  (test-run)
 *
 * Veilig om opnieuw te starten — pakt waar 'ie gebleven was.
 */
class BlogBackfillTranslations extends Command
{
	protected $signature = 'blog:backfill-translations
		{locales* : Doel-locales (bv. en de fr es)}
		{--source=nl : Bron-locale}
		{--limit=0 : Max aantal posts om te verwerken (0 = alle)}
		{--delay=1 : Aantal sec wachten tussen calls (rate-limit-buffer)}';

	protected $description = 'Vertaal alle bestaande source-locale blog-posts naar target-locales (idempotent).';

	public function handle(BlogTranslator $translator): int
	{
		$sourceLocale = (string) $this->option('source');
		$targets = $this->argument('locales');
		$limit = max(0, (int) $this->option('limit'));
		$delay = max(0, (int) $this->option('delay'));

		$query = BlogPost::query()
			->where('locale', $sourceLocale)
			->published()
			->orderBy('id');
		if ($limit > 0) {
			$query->limit($limit);
		}
		$posts = $query->get();
		$this->info("Backfill: {$posts->count()} {$sourceLocale}-posts × " . count($targets) . ' talen = max ' . ($posts->count() * count($targets)) . ' Claude-calls.');

		$totalOk = 0; $totalSkip = 0; $totalFail = 0;
		$startTime = microtime(true);

		foreach ($posts as $i => $post) {
			$idx = $i + 1;
			foreach ($targets as $target) {
				$target = (string) $target;
				// Idempotent check vóór de Claude-call — voorkomt nutteloze API-roundtrip
				$existing = BlogPost::where('translation_of_post_id', $post->id)
					->where('locale', $target)
					->exists();
				if ($existing) {
					$totalSkip++;
					continue;
				}

				try {
					$tr = $translator->translate($post, $target);
					$totalOk++;
					$elapsed = round(microtime(true) - $startTime);
					$this->line(sprintf(
						'[%d/%d] ✓ %s → %s  (%s)  [+%d ok, %d skip, %d fail, %ds]',
						$idx, $posts->count(), $post->slug, $target,
						mb_strimwidth($tr->title, 0, 50, '…'),
						$totalOk, $totalSkip, $totalFail, $elapsed
					));
					if ($delay > 0) sleep($delay);
				} catch (Throwable $e) {
					$totalFail++;
					$this->warn(sprintf(
						'[%d/%d] ✕ %s → %s : %s',
						$idx, $posts->count(), $post->slug, $target, $e->getMessage()
					));
				}
			}
		}

		$elapsed = round(microtime(true) - $startTime);
		$this->info("\nKlaar in {$elapsed}s. OK={$totalOk} skip={$totalSkip} fail={$totalFail}");
		return $totalFail > 0 ? self::FAILURE : self::SUCCESS;
	}
}
