<?php

namespace App\Services;

class IbanValidator
{
	private const COMPANY_KEYWORDS = ['BV', 'B.V.', 'NV', 'N.V.', 'GMBH', 'LTD', 'INC', 'LLC', 'SA', 'SRL', 'S.L.', 'SL'];

	public function check(string $name, string $iban): array
	{
		$cleanIban = $this->cleanIban($iban);
		$valid = $this->isValidIban($cleanIban);

		$risks = [];

		if (! $valid) {
			$risks[] = 'risk_invalid';
		} else {
			if (mb_strlen($name) > 0 && mb_strlen($name) < 4) {
				$risks[] = 'risk_short_name';
			}
			if ($this->containsCompanyKeyword($name)) {
				$risks[] = 'risk_company';
			}
			if ($this->nameMismatchRisk($name)) {
				$risks[] = 'risk_name_mismatch';
			}
			if (preg_match('/^(TEST|DUMMY|UNKNOWN|ONBEKEND|NA|N\/A)$/i', trim($name))) {
				$risks[] = 'risk_suspicious_name';
			}
		}

		return [
			'valid' => $valid,
			'iban_clean' => $cleanIban,
			'risks' => $risks,
		];
	}

	private function cleanIban(string $iban): string
	{
		return (string) preg_replace('/\s+/', '', strtoupper(trim($iban)));
	}

	private function isValidIban(string $iban): bool
	{
		if ($iban === '' || ! preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$/', $iban)) {
			return false;
		}

		$rearranged = substr($iban, 4) . substr($iban, 0, 4);

		$numeric = '';
		$len = strlen($rearranged);
		for ($i = 0; $i < $len; $i++) {
			$ch = $rearranged[$i];
			$numeric .= ($ch >= 'A' && $ch <= 'Z') ? (string) (ord($ch) - 55) : $ch;
		}

		$checksum = 0;
		$lenN = strlen($numeric);
		for ($i = 0; $i < $lenN; $i++) {
			$checksum = ($checksum * 10 + (int) $numeric[$i]) % 97;
		}

		return $checksum === 1;
	}

	private function containsCompanyKeyword(string $name): bool
	{
		if ($name === '') {
			return false;
		}
		foreach (self::COMPANY_KEYWORDS as $kw) {
			if (stripos($name, $kw) !== false) {
				return true;
			}
		}
		return false;
	}

	private function nameMismatchRisk(string $name): bool
	{
		$name = trim($name);
		if ($name === '') {
			return false;
		}
		$wordCount = str_word_count($name);
		if ($wordCount <= 1) {
			return true;
		}
		$hasCompany = $this->containsCompanyKeyword($name);
		if ($hasCompany && $wordCount >= 4) {
			return true;
		}
		if (! $hasCompany && $wordCount >= 5) {
			return true;
		}
		return false;
	}
}
