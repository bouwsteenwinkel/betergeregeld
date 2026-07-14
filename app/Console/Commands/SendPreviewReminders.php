<?php

namespace App\Console\Commands;

use App\Services\PreviewReminderService;
use Illuminate\Console\Command;

class SendPreviewReminders extends Command
{
    protected $signature = 'previews:send-reminders';

    protected $description = 'Stuurt reminder-mails naar klanten die een voorbeeld opsloegen (dag 1 en dag 4).';

    public function handle(PreviewReminderService $service): int
    {
        $r = $service->run();
        $this->info("Preview-reminders verstuurd: {$r['sent']}, overgeslagen: {$r['skipped']}.");

        return self::SUCCESS;
    }
}
