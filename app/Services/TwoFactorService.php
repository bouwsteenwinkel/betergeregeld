<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTwofa;
use App\Models\UserTwofaBackupCode;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQR;

class TwoFactorService
{
	private Google2FA $g2fa;
	private Google2FAQR $g2faQr;

	public function __construct()
	{
		$this->g2fa = new Google2FA();
		$this->g2faQr = new Google2FAQR();
	}

	public function generateSecret(): string
	{
		return $this->g2fa->generateSecretKey();
	}

	public function qrDataUri(User $user, string $secret): string
	{
		return $this->g2faQr->getQRCodeInline(
			(string) config('app.name'),
			$user->email,
			$secret
		);
	}

	public function verifyTotp(string $secret, string $code): bool
	{
		$code = preg_replace('/\s+/', '', $code);
		return $this->g2fa->verifyKey($secret, (string) $code, 1);
	}

	public function enable(User $user, string $secret): array
	{
		$backupCodes = $this->generateBackupCodes();

		DB::transaction(function () use ($user, $secret, $backupCodes) {
			UserTwofa::updateOrCreate(
				['user_id' => $user->id],
				[
					'enabled' => true,
					'secret_enc' => Crypt::encryptString($secret),
					'confirmed_at' => now(),
				]
			);

			UserTwofaBackupCode::where('user_id', $user->id)->delete();

			foreach ($backupCodes as $code) {
				UserTwofaBackupCode::create([
					'user_id' => $user->id,
					'code_hash' => hash('sha256', $code),
				]);
			}
		});

		return $backupCodes;
	}

	public function disable(User $user): void
	{
		DB::transaction(function () use ($user) {
			UserTwofa::where('user_id', $user->id)->delete();
			UserTwofaBackupCode::where('user_id', $user->id)->delete();
		});
	}

	public function isEnabled(User $user): bool
	{
		return UserTwofa::where('user_id', $user->id)->where('enabled', true)->exists();
	}

	public function decryptSecret(UserTwofa $tfa): string
	{
		return Crypt::decryptString((string) $tfa->secret_enc);
	}

	public function consumeBackupCode(User $user, string $code): bool
	{
		$hash = hash('sha256', preg_replace('/\s+/', '', strtoupper($code)));

		$row = UserTwofaBackupCode::where('user_id', $user->id)
			->where('code_hash', $hash)
			->whereNull('used_at')
			->first();

		if (! $row) {
			return false;
		}

		$row->update(['used_at' => now()]);

		return true;
	}

	private function generateBackupCodes(int $count = 10): array
	{
		$codes = [];
		for ($i = 0; $i < $count; $i++) {
			$codes[] = strtoupper(Str::random(10));
		}
		return $codes;
	}
}
