<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		DB::statement("
			CREATE TABLE `contact_messages` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`public_id` char(16) NOT NULL,
				`created_at` datetime NOT NULL,
				`status` varchar(30) NOT NULL DEFAULT 'new',
				`name` varchar(120) NOT NULL,
				`email` varchar(190) NOT NULL,
				`topic` varchar(100) DEFAULT NULL,
				`website` varchar(255) DEFAULT NULL,
				`company` varchar(190) DEFAULT NULL,
				`phone` varchar(60) DEFAULT NULL,
				`cms_platform` varchar(80) DEFAULT NULL,
				`traffic` varchar(40) DEFAULT NULL,
				`needs_json` longtext DEFAULT NULL,
				`subject` varchar(190) NOT NULL,
				`message` text NOT NULL,
				`ip` varchar(45) NOT NULL,
				`user_agent` varchar(255) NOT NULL,
				`referer` varchar(255) DEFAULT NULL,
				`page_uri` varchar(255) NOT NULL,
				`session_id` varchar(128) NOT NULL,
				`payload_hash` char(64) NOT NULL,
				`payload_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload_json`)),
				`user_id` char(36) DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `uq_public_id` (`public_id`),
				KEY `idx_created_at` (`created_at`),
				KEY `idx_status` (`status`),
				KEY `idx_email` (`email`),
				KEY `idx_user_id` (`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		");
	}

	public function down(): void
	{
		DB::statement('DROP TABLE IF EXISTS `contact_messages`');
	}
};
