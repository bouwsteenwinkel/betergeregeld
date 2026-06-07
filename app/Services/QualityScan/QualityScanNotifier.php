<?php

namespace App\Services\QualityScan;

use App\Models\Quality\QualityScan;
use Illuminate\Support\Facades\Mail;

/**
 * Stuurt een mail-notificatie bij relevante kwaliteitsregressies: een nieuwe
 * bevinding met severity 'hoog', een scoredaling boven de drempel, of een
 * gefaalde noindex-detectie. Mailt naar config('quality-scan.notify_email').
 */
class QualityScanNotifier
{
	public function evaluate(QualityScan $scan): void
	{
		$to = (string) config('quality-scan.notify_email');
		if ($to === '') {
			return;
		}

		$scan->loadMissing('findings', 'page.site');
		$previous = QualityScan::where('monitored_page_id', $scan->monitored_page_id)
			->where('status', 'completed')->where('id', '!=', $scan->id)
			->latest('completed_at')->with('findings')->first();

		$reasons = [];

		// 1. Nieuwe bevinding(en) met severity hoog (status fail/warn).
		$prevKeys = $previous
			? $previous->findings->map(fn ($f) => $f->check_id . '|' . $f->element)->all()
			: [];
		$newHigh = $scan->findings
			->where('severity', 'hoog')->whereIn('status', ['fail', 'warn'])
			->reject(fn ($f) => in_array($f->check_id . '|' . $f->element, $prevKeys, true));
		if ($newHigh->isNotEmpty()) {
			$reasons[] = $newHigh->count() . ' nieuwe bevinding(en) met hoge ernst.';
		}

		// 2. Scoredaling boven de drempel.
		$drop = (int) config('quality-scan.score_drop_alert', 15);
		if ($previous && $previous->score !== null && $scan->score !== null && ($previous->score - $scan->score) > $drop) {
			$reasons[] = "De score daalde van {$previous->score} naar {$scan->score} ({$previous->score} → {$scan->score}).";
		}

		// 3. noindex-detectie faalt.
		if ($scan->findings->first(fn ($f) => $f->check_id === 'noindex_detectie' && $f->status === 'fail')) {
			$reasons[] = 'De pagina staat op noindex (wordt niet door Google geïndexeerd).';
		}

		if (empty($reasons)) {
			return;
		}

		$page = $scan->page;
		$label = $page?->label ?? 'pagina';
		$url = $page?->url ?? '';
		$subject = "[Kwaliteit] {$label}: aandachtspunten (score {$scan->score})";
		$body = "De kwaliteitsscan van '{$label}' ({$url}) vraagt aandacht:\n\n"
			. '• ' . implode("\n• ", $reasons) . "\n\n"
			. "Totaalscore: {$scan->score}/100\n"
			. 'Fails: ' . $scan->findings->where('status', 'fail')->count()
			. ' · Warns: ' . $scan->findings->where('status', 'warn')->count();

		Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
	}
}
