<?php

namespace App\Services;

class PostcodeValidator
{
	public function check(string $postcode, string $city, string $street, string $houseNumber): array
	{
		$postcodeClean = strtoupper((string) preg_replace('/\s+/', '', trim($postcode)));
		$city = trim($city);
		$street = trim($street);
		$houseNo = trim($houseNumber);

		if ($postcodeClean === '' && $city === '' && $street === '' && $houseNo === '') {
			return [
				'valid' => false,
				'country' => 'UNKNOWN',
				'normalized' => compact('postcodeClean', 'city', 'street') + ['house_number' => $houseNo],
				'errors' => ['postcode.empty_input'],
				'warnings' => [],
				'info' => [],
			];
		}

		$country = $this->detectCountry($postcodeClean);
		$errors = [];
		$warnings = [];
		$info = [];

		if ($city !== '' && preg_match('/\d/', $city)) {
			$warnings[] = 'postcode.warn_city_digits';
		}
		if ($street !== '' && preg_match('/^\d+$/', $street)) {
			$warnings[] = 'postcode.warn_street_only_digits';
		}

		if ($houseNo !== '') {
			if (! preg_match('/^\d+[A-Z0-9\-\/]*$/i', $houseNo)) {
				$warnings[] = 'postcode.warn_house_format';
			}
		} else {
			$warnings[] = 'postcode.warn_house_missing';
		}

		if ($postcodeClean === '') {
			$errors[] = 'postcode.err_postcode_missing';
		} elseif ($country === 'NL') {
			if (! preg_match('/^[1-9][0-9]{3}[A-Z]{2}$/', $postcodeClean)) {
				$errors[] = 'postcode.err_nl_format';
			} else {
				$suffix = substr($postcodeClean, 4, 2);
				if (in_array($suffix, ['SA', 'SD', 'SS'], true)) {
					$warnings[] = 'postcode.warn_nl_suffix';
				}
				$info[] = 'postcode.info_detected_nl';
			}
		} elseif ($country === 'BE') {
			if (! preg_match('/^[1-9][0-9]{3}$/', $postcodeClean)) {
				$errors[] = 'postcode.err_be_format';
			} else {
				$info[] = 'postcode.info_detected_be';
			}
		} elseif ($country === 'DE') {
			if (! preg_match('/^[0-9]{5}$/', $postcodeClean)) {
				$errors[] = 'postcode.err_de_format';
			} else {
				$info[] = 'postcode.info_detected_de';
			}
		} else {
			$warnings[] = 'postcode.warn_unknown_country';
		}

		if ($country === 'NL' && $postcodeClean !== '' && $city !== '' && mb_strlen($city) < 3) {
			$warnings[] = 'postcode.warn_city_short';
		}

		return [
			'valid' => count($errors) === 0,
			'country' => $country,
			'normalized' => [
				'postcode' => $postcodeClean,
				'city' => $city,
				'street' => $street,
				'house_number' => $houseNo,
			],
			'errors' => $errors,
			'warnings' => $warnings,
			'info' => $info,
		];
	}

	private function detectCountry(string $postcode): string
	{
		if ($postcode === '') {
			return 'UNKNOWN';
		}
		if (preg_match('/^[1-9][0-9]{3}[A-Z]{2}$/', $postcode)) {
			return 'NL';
		}
		if (preg_match('/^[1-9][0-9]{3}$/', $postcode)) {
			return 'BE';
		}
		if (preg_match('/^[0-9]{5}$/', $postcode)) {
			return 'DE';
		}
		return 'UNKNOWN';
	}
}
