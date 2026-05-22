<?php

namespace App\Services\AccessGuard\Radar\Vulnerabilities;

/**
 * Maps the vendor/product strings used by external CVE feeds onto the
 * canonical (vendor, product) pair we store in software_instances and
 * software_catalog. Without this, the matcher would miss findings
 * because "WordPress" / "wordpress" / "Wordpress" would all be distinct.
 *
 * Lookup is case-insensitive and trims whitespace. Unknown inputs are
 * returned in lowercased form — better to have *some* match attempt
 * than to drop the row entirely; the matcher's confidence score will
 * reflect that the mapping was best-effort.
 */
final class VendorProductNormalizer
{
	/** External (vendor, product) → canonical (vendor, product) */
	private const ALIASES = [
		// CISA KEV style
		'wordpress|wordpress'       => ['wordpress', 'wordpress'],
		'wordpress foundation|wordpress' => ['wordpress', 'wordpress'],
		'apache|http server'        => ['apache', 'httpd'],
		'apache|httpd'              => ['apache', 'httpd'],
		'apache software foundation|http server' => ['apache', 'httpd'],
		'f5|nginx'                  => ['nginx', 'nginx'],
		'nginx, inc.|nginx'         => ['nginx', 'nginx'],
		'php group|php'             => ['php', 'php'],
		'the php group|php'         => ['php', 'php'],
		'oracle|mysql'              => ['mysql', 'mysql'],
		'mariadb|mariadb server'    => ['mariadb', 'mariadb'],
		'node.js|node.js'           => ['nodejs', 'nodejs'],
		'openjs foundation|node.js' => ['nodejs', 'nodejs'],
		'drupal|drupal core'        => ['drupal', 'drupal'],
		'drupal|drupal'             => ['drupal', 'drupal'],
		'joomla|joomla!'            => ['joomla', 'joomla'],
		'joomla!|joomla!'           => ['joomla', 'joomla'],
		'magento|magento'           => ['magento', 'magento'],
		'adobe|magento'             => ['magento', 'magento'],

		// OSV style (ecosystem|package)
		'packagist|wordpress/core'  => ['wordpress', 'wordpress'],
		'wordpress|core'            => ['wordpress', 'wordpress'],
	];

	/** @return array{0:string,1:string} */
	public function normalise(string $vendor, string $product): array
	{
		$key = strtolower(trim($vendor)) . '|' . strtolower(trim($product));
		if (isset(self::ALIASES[$key])) {
			return self::ALIASES[$key];
		}
		return [strtolower(trim($vendor)), strtolower(trim($product))];
	}
}
