<?php
/**
 * Plugin Name: Beter Geregeld — Security Monitor
 * Description: Rapporteert dagelijks WordPress-core, plugins en thema's (versies + beschikbare updates) aan het Beter Geregeld / Rankdata-monitoringplatform, zodat verouderde en kwetsbare software wordt gesignaleerd.
 * Version: 1.0.0
 * Author: Beter Geregeld
 *
 * INSTALLATIE: plak deze map/dit bestand in wp-content/plugins/, vul hieronder
 * de TOKEN in (uit het Rankdata-admin → GSC Properties → Beveiligingsagent),
 * en activeer de plugin. Hij pusht dan dagelijks (en bij activatie).
 */

if (! defined('ABSPATH')) {
	exit;
}

// === Instellen ===========================================================
// Endpoint van het platform (zonder token):
if (! defined('BG_MONITOR_ENDPOINT')) {
	define('BG_MONITOR_ENDPOINT', 'https://betergeregeld.com/security/ingest/');
}
// Per-site token — vervang dit, of zet define('BG_MONITOR_TOKEN','...') in wp-config.php:
if (! defined('BG_MONITOR_TOKEN')) {
	define('BG_MONITOR_TOKEN', 'PLAK-HIER-DE-TOKEN');
}
// =========================================================================

/** Verzamelt core/plugins/thema's met versie + update-status. */
function bg_monitor_collect() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/update.php';
	require_once ABSPATH . 'wp-admin/includes/theme.php';

	// Verse update-info ophalen.
	wp_version_check();
	wp_update_plugins();
	wp_update_themes();

	$components = array();

	// Core.
	global $wp_version;
	$core_has_update = false;
	$core_updates = function_exists('get_core_updates') ? get_core_updates() : array();
	if (is_array($core_updates)) {
		foreach ($core_updates as $u) {
			if (isset($u->response) && 'upgrade' === $u->response) {
				$core_has_update = true;
			}
		}
	}
	$components[] = array(
		'type' => 'core', 'slug' => 'wordpress', 'name' => 'WordPress',
		'version' => $wp_version, 'has_update' => $core_has_update, 'active' => true,
	);

	// Plugins.
	$all_plugins = get_plugins();
	$plugin_updates = get_plugin_updates();
	$active_plugins = (array) get_option('active_plugins', array());
	foreach ($all_plugins as $file => $data) {
		$slug = dirname($file);
		if ('.' === $slug || '' === $slug) {
			$slug = basename($file, '.php');
		}
		$components[] = array(
			'type' => 'plugin', 'slug' => $slug,
			'name' => isset($data['Name']) ? $data['Name'] : $slug,
			'version' => isset($data['Version']) ? $data['Version'] : null,
			'latest_version' => isset($plugin_updates[$file]->update->new_version) ? $plugin_updates[$file]->update->new_version : null,
			'has_update' => isset($plugin_updates[$file]),
			'active' => in_array($file, $active_plugins, true),
		);
	}

	// Thema's.
	$themes = wp_get_themes();
	$theme_updates = get_theme_updates();
	$active_theme = get_stylesheet();
	foreach ($themes as $slug => $theme) {
		$components[] = array(
			'type' => 'theme', 'slug' => $slug,
			'name' => $theme->get('Name') ? $theme->get('Name') : $slug,
			'version' => $theme->get('Version') ? $theme->get('Version') : null,
			'latest_version' => isset($theme_updates[$slug]->update['new_version']) ? $theme_updates[$slug]->update['new_version'] : null,
			'has_update' => isset($theme_updates[$slug]),
			'active' => ($slug === $active_theme),
		);
	}

	return $components;
}

/**
 * File-integrity van WP-core: vergelijkt elk core-bestand tegen de officiële
 * WP.org-checksums en geeft de afwijkingen terug (gewijzigd/ontbrekend).
 * wp-content (gebruikersbestanden) wordt overgeslagen.
 */
function bg_monitor_integrity() {
	require_once ABSPATH . 'wp-admin/includes/update.php';
	global $wp_version;

	$locale = get_locale();
	$checksums = function_exists('get_core_checksums') ? get_core_checksums($wp_version, $locale ? $locale : 'en_US') : false;
	if (! is_array($checksums) || empty($checksums)) {
		return array('checked' => false, 'issues' => array());
	}

	$issues = array();
	foreach ($checksums as $file => $md5) {
		if (0 === strpos($file, 'wp-content/')) {
			continue; // geen core
		}
		$path = ABSPATH . $file;
		if (! file_exists($path)) {
			$issues[] = array('type' => 'missing', 'path' => $file);
			continue;
		}
		if (md5_file($path) !== $md5) {
			$issues[] = array('type' => 'modified', 'path' => $file);
		}
		if (count($issues) >= 200) {
			break; // begrenzing
		}
	}

	// Onverwachte PHP-bestanden in de core-mappen (mogelijke malware-injectie):
	// alles in wp-admin/wp-includes dat niet in de officiële checksums staat.
	$expected = array_fill_keys(array_keys($checksums), true);
	foreach (array('wp-admin', 'wp-includes') as $dir) {
		$base = ABSPATH . $dir;
		if (! is_dir($base) || count($issues) >= 200) {
			continue;
		}
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($iterator as $info) {
			if (count($issues) >= 200) {
				break;
			}
			if (! $info->isFile() || 'php' !== strtolower($info->getExtension())) {
				continue;
			}
			$rel = str_replace('\\', '/', substr($info->getPathname(), strlen(ABSPATH)));
			if (! isset($expected[$rel])) {
				$issues[] = array('type' => 'unexpected', 'path' => $rel);
			}
		}
	}

	return array('checked' => true, 'issues' => $issues);
}

/** Pusht de inventaris + integriteit naar het platform. */
function bg_monitor_push() {
	if (! defined('BG_MONITOR_TOKEN') || 'PLAK-HIER-DE-TOKEN' === BG_MONITOR_TOKEN) {
		return;
	}
	$url = rtrim(BG_MONITOR_ENDPOINT, '/') . '/' . rawurlencode(BG_MONITOR_TOKEN);
	wp_remote_post($url, array(
		'timeout' => 30,
		'headers' => array('Content-Type' => 'application/json', 'Accept' => 'application/json'),
		'body'    => wp_json_encode(array(
			'components' => bg_monitor_collect(),
			'integrity'  => bg_monitor_integrity(),
		)),
	));
}

add_action('bg_monitor_daily', 'bg_monitor_push');

register_activation_hook(__FILE__, function () {
	if (! wp_next_scheduled('bg_monitor_daily')) {
		wp_schedule_event(time() + 60, 'daily', 'bg_monitor_daily');
	}
});

register_deactivation_hook(__FILE__, function () {
	wp_clear_scheduled_hook('bg_monitor_daily');
});
