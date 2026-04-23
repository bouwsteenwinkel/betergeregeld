<?php

namespace App\Console\Commands;

use App\Services\AccessGuard\NotificationService;
use Illuminate\Console\Command;

class AccessGuardSendDigests extends Command
{
	protected $signature = 'accessguard:send-digests';

	protected $description = 'Send the daily AccessGuard digest email to every opted-in user.';

	public function handle(NotificationService $notifier): int
	{
		$res = $notifier->sendDigests();
		$this->info("Sent: {$res['sent']}  ·  Skipped: {$res['skipped']}");
		return self::SUCCESS;
	}
}
