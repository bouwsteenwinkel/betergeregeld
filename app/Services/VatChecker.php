<?php

namespace App\Services;

use SoapClient;
use SoapFault;
use Throwable;

class VatChecker
{
	private const WSDL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	public function check(string $country, string $vat): array
	{
		$country = strtoupper(trim($country));
		$vat = strtoupper(trim($vat));

		if ($country === '' && preg_match('/^[A-Z]{2}/', $vat)) {
			$country = substr($vat, 0, 2);
			$vat = substr($vat, 2);
		}

		$vat = (string) preg_replace('/[^A-Z0-9]/', '', $vat);

		if (! preg_match('/^[A-Z]{2}$/', $country)) {
			return [
				'ok' => false,
				'error_key' => 'vat.err_country_invalid',
				'input' => ['country' => $country, 'vat' => $vat, 'formatted' => $country . $vat],
			];
		}

		if (strlen($vat) < 4) {
			return [
				'ok' => false,
				'error_key' => 'vat.err_vat_too_short',
				'input' => ['country' => $country, 'vat' => $vat, 'formatted' => $country . $vat],
			];
		}

		try {
			$client = new SoapClient(self::WSDL, [
				'exceptions' => true,
				'trace' => false,
				'cache_wsdl' => WSDL_CACHE_BOTH,
				'connection_timeout' => 10,
			]);

			$res = $client->checkVat([
				'countryCode' => $country,
				'vatNumber' => $vat,
			]);

			return [
				'ok' => true,
				'input' => ['country' => $country, 'vat' => $vat, 'formatted' => $country . $vat],
				'vies' => [
					'valid' => (bool) ($res->valid ?? false),
					'countryCode' => (string) ($res->countryCode ?? $country),
					'vatNumber' => (string) ($res->vatNumber ?? $vat),
					'name' => $this->cleanViesString($res->name ?? ''),
					'address' => $this->cleanViesString($res->address ?? ''),
					'requestDate' => isset($res->requestDate) ? $this->formatSoapDate($res->requestDate) : null,
				],
			];
		} catch (SoapFault $e) {
			return [
				'ok' => false,
				'error_key' => 'vat.err_vies_unavailable',
				'error_detail' => trim((string) $e->getMessage()),
				'input' => ['country' => $country, 'vat' => $vat, 'formatted' => $country . $vat],
			];
		} catch (Throwable $e) {
			return [
				'ok' => false,
				'error_key' => 'vat.err_server',
				'error_detail' => $e->getMessage(),
				'input' => ['country' => $country, 'vat' => $vat, 'formatted' => $country . $vat],
			];
		}
	}

	private function cleanViesString(mixed $value): string
	{
		$s = trim((string) $value);
		$s = (string) preg_replace("/\r\n|\r|\n/", "\n", $s);
		return (string) preg_replace("/[ \t]+/", ' ', $s);
	}

	private function formatSoapDate(mixed $soapDate): ?string
	{
		if ($soapDate instanceof \DateTimeInterface) {
			return $soapDate->format('Y-m-d');
		}
		$s = trim((string) $soapDate);
		return $s !== '' ? $s : null;
	}
}
