<?php

namespace App\Console\Commands;

use App\Http\Middleware\SetLocale;
use App\Models\Blog\BlogPost;
use App\Services\Blog\BlogGenerator;
use App\Services\Blog\BlogTranslator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Genereer elke dag een nieuwe blog-post (NL + EN-vertaling) via Claude
 * en stuur Dennis een mail met directe links naar beide versies.
 *
 * Gepland in routes/console.php dailyAt 09:00. Idempotent: bij een
 * tweede run op dezelfde dag maakt 'ie gewoon een nieuwe post — wij
 * gokken dat de scheduler niet 2× per dag triggert. Voor extra
 * veiligheid kun je --skip-if-todays-post gebruiken.
 */
class BlogGenerateDaily extends Command
{
	protected $signature = 'blog:generate-daily
		{--no-translate : skip EN-vertaling}
		{--no-mail : skip notify-mail}
		{--skip-if-todays-post : doe niets als er al een post van vandaag is}';

	protected $description = 'Genereer een nieuwe NL-blog-post (+EN-vertaling) via Claude en publiceer direct.';

	public function handle(BlogGenerator $generator, BlogTranslator $translator): int
	{
		if ($this->option('skip-if-todays-post')) {
			$today = now()->toDateString();
			$existing = BlogPost::where('locale', 'nl')
				->whereDate('published_at', $today)
				->exists();
			if ($existing) {
				$this->info('Skip — er is vandaag al een NL-post gepubliceerd.');
				return self::SUCCESS;
			}
		}

		$this->info('Genereer NL-post via Claude…');
		try {
			$nl = $generator->generate();
		} catch (Throwable $e) {
			$this->error('Generatie mislukt: ' . $e->getMessage());
			return self::FAILURE;
		}
		$this->info("✓ NL gepubliceerd: {$nl->title}  ({$nl->slug})");

		$translations = [];
		if (!$this->option('no-translate')) {
			$targets = array_values(array_diff(SetLocale::SUPPORTED, ['nl']));
			foreach ($targets as $loc) {
				$this->info("Vertaal naar {$loc}…");
				try {
					$tr = $translator->translate($nl, $loc);
					$translations[$loc] = $tr;
					$this->info("✓ {$loc} gepubliceerd: {$tr->title}  ({$tr->slug})");
				} catch (Throwable $e) {
					$this->warn("Vertaling {$loc} mislukt: " . $e->getMessage());
				}
			}
		}

		if (!$this->option('no-mail')) {
			$this->sendNotification($nl, $translations);
		}

		return self::SUCCESS;
	}

	/**
	 * @param array<string,BlogPost> $translations
	 */
	private function sendNotification(BlogPost $nl, array $translations): void
	{
		$to = env('BLOG_NOTIFY_TO', 'dennis@bouwsteenwinkel.nl');
		$base = rtrim(env('APP_URL', 'https://betergeregeld.com'), '/');
		$nlUrl = "{$base}/nl/blog/{$nl->slug}";

		$subject = 'Nieuwe blog online: ' . $nl->title;

		$lines = [
			"Hoi Dennis,",
			"",
			"Een nieuwe blog-post is automatisch gepubliceerd op betergeregeld.com.",
			"",
			"NL:  {$nlUrl}",
		];
		foreach (SetLocale::SUPPORTED as $loc) {
			if ($loc === 'nl') continue;
			if (isset($translations[$loc])) {
				$lines[] = strtoupper($loc) . ":  {$base}/{$loc}/blog/" . $translations[$loc]->slug;
			} else {
				$lines[] = strtoupper($loc) . ":  (vertaling mislukt)";
			}
		}
		$lines[] = "";
		$lines[] = "Categorie:    " . ($nl->category->name ?? '?');
		$lines[] = "Leestijd:     {$nl->reading_time_min} min";
		$lines[] = "Tags:         " . $nl->tags->pluck('name')->implode(', ');
		$lines[] = "Excerpt:      " . $nl->excerpt;
		$lines[] = "";
		$lines[] = "Klik op de links hierboven om te reviewen.";
		$lines[] = "";
		$lines[] = "— Beter Geregeld automatisering";

		$text = implode("\n", $lines);

		$linkRows = '<p><strong>NL:</strong> <a href="' . e($nlUrl) . '">' . e($nlUrl) . '</a><br>';
		foreach (SetLocale::SUPPORTED as $loc) {
			if ($loc === 'nl') continue;
			if (isset($translations[$loc])) {
				$url = "{$base}/{$loc}/blog/" . $translations[$loc]->slug;
				$linkRows .= '<strong>' . strtoupper($loc) . ':</strong> <a href="' . e($url) . '">' . e($url) . '</a><br>';
			} else {
				$linkRows .= '<strong>' . strtoupper($loc) . ':</strong> <em>vertaling mislukt</em><br>';
			}
		}
		$linkRows .= '</p>';

		$html = '<p>Hoi Dennis,</p>'
			. '<p>Een nieuwe blog-post is automatisch gepubliceerd op betergeregeld.com.</p>'
			. $linkRows
			. '<table style="font-size:13px;border-collapse:collapse"><tbody>'
			. '<tr><td style="padding:2px 12px 2px 0">Categorie</td><td>' . e($nl->category->name ?? '?') . '</td></tr>'
			. '<tr><td style="padding:2px 12px 2px 0">Leestijd</td><td>' . (int) $nl->reading_time_min . ' min</td></tr>'
			. '<tr><td style="padding:2px 12px 2px 0">Tags</td><td>' . e($nl->tags->pluck('name')->implode(', ')) . '</td></tr>'
			. '<tr><td style="padding:2px 12px 2px 0;vertical-align:top">Excerpt</td><td>' . e($nl->excerpt) . '</td></tr>'
			. '</tbody></table>'
			. '<p>Klik op de links om te reviewen.</p>'
			. '<p style="color:#666;font-size:12px">— Beter Geregeld automatisering</p>';

		try {
			Mail::raw('', function ($m) use ($to, $subject, $text, $html) {
				$m->to($to)
					->subject($subject)
					->html($html)
					->text($text);
			});
			$this->info("✓ Notify-mail verzonden naar {$to}");
		} catch (Throwable $e) {
			$this->warn('Mail mislukt: ' . $e->getMessage());
		}
	}
}
