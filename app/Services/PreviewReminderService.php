<?php

namespace App\Services;

use App\Mail\PreviewReminderMail;
use App\Models\WebsiteLead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Stuurt reminder-mails naar klanten die een voorbeeld opsloegen maar nog niets van
 * zich lieten horen. Cadence: bevestiging op dag 0 (bij opslaan), reminder op dag 1
 * (stage 0) en dag 4 (stage 1). Stopt automatisch bij opt-out, of zodra het team de
 * lead oppakt (status != 'new'), of als er geen opgeslagen preview meer is.
 */
class PreviewReminderService
{
    /** @return array{sent:int,skipped:int} */
    public function run(): array
    {
        $sent = 0;
        $skipped = 0;

        $leads = WebsiteLead::query()
            ->where('consent', true)
            ->whereNull('unsubscribed_at')
            ->where('reminder_stage', '<', 2)
            ->whereNotNull('next_reminder_at')
            ->where('next_reminder_at', '<=', now())
            ->where('status', 'new')
            ->get();

        foreach ($leads as $lead) {
            if (! $lead->savedPreviews()->exists()) {
                $lead->update(['next_reminder_at' => null]);
                $skipped++;
                continue;
            }

            $stage = (int) $lead->reminder_stage;      // 0 -> dag1, 1 -> dag4
            $kind = $stage === 0 ? 'dag1' : 'dag4';

            try {
                Mail::to($lead->email)->send(new PreviewReminderMail($lead, $kind));
            } catch (\Throwable $e) {
                Log::warning('preview_reminder_mail: ' . $e->getMessage());
                $skipped++;
                continue;
            }

            $newStage = $stage + 1;
            $next = $newStage >= 2
                ? null
                : optional($lead->saved_at)->copy()?->addDays(4) ?? now()->addDays(3);

            $lead->update(['reminder_stage' => $newStage, 'next_reminder_at' => $next]);
            $sent++;
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
