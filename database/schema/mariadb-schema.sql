/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `event` varchar(60) NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `page_uri` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`meta_json`)),
  `meta_hash` char(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_event` (`event`),
  KEY `idx_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `case_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `case_events` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `case_id` char(36) NOT NULL,
  `ts` datetime NOT NULL DEFAULT current_timestamp(),
  `actor` varchar(20) NOT NULL DEFAULT 'system',
  `channel` varchar(20) NOT NULL DEFAULT 'api',
  `event_type` varchar(30) NOT NULL DEFAULT 'note',
  `message` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  PRIMARY KEY (`id`),
  KEY `ix_case_events_tenant_case_ts` (`tenant_id`,`case_id`,`ts`),
  KEY `fk_case_events_case` (`case_id`),
  CONSTRAINT `fk_case_events_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_case_events_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cases` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `case_type` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `severity` varchar(10) NOT NULL DEFAULT 'low',
  `confidence` decimal(4,3) NOT NULL DEFAULT 0.000,
  `auto_decision` varchar(40) DEFAULT NULL,
  `reason_code` varchar(60) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `assigned_to` char(36) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_cases_one_active_per_order_type` (`tenant_id`,`order_id`,`case_type`),
  KEY `ix_cases_tenant_status` (`tenant_id`,`status`),
  KEY `ix_cases_tenant_severity` (`tenant_id`,`severity`),
  KEY `ix_cases_tenant_updated` (`tenant_id`,`updated_at`),
  KEY `ix_cases_assigned_to` (`tenant_id`,`assigned_to`),
  KEY `fk_cases_order` (`order_id`),
  KEY `fk_cases_user` (`assigned_to`),
  CONSTRAINT `fk_cases_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cases_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cases_user` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_branding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_branding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `branding_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`branding_json`)),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant` (`tenant_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `key` varchar(32) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_key` (`tenant_key`,`key`),
  KEY `idx_tenant` (`tenant_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_consent_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_consent_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `consent_id` char(36) NOT NULL,
  `event` enum('created','updated','withdrawn') NOT NULL,
  `policy_version` int(11) NOT NULL,
  `choices_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`choices_json`)),
  `ip_hash` char(64) DEFAULT NULL,
  `ua_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_consent` (`tenant_key`,`consent_id`),
  KEY `fk_cmp_logs_domain` (`domain_id`),
  CONSTRAINT `fk_cmp_logs_domain` FOREIGN KEY (`domain_id`) REFERENCES `cmp_domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_consents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `consent_id` char(36) NOT NULL,
  `policy_version` int(11) NOT NULL DEFAULT 1,
  `status` enum('accepted','rejected','custom') NOT NULL,
  `choices_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`choices_json`)),
  `first_seen_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_consent` (`tenant_key`,`consent_id`),
  KEY `idx_tenant_domain` (`tenant_key`,`domain_id`),
  KEY `fk_cmp_consents_domain` (`domain_id`),
  CONSTRAINT `fk_cmp_consents_domain` FOREIGN KEY (`domain_id`) REFERENCES `cmp_domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_domain` (`tenant_key`,`domain`),
  KEY `idx_tenant` (`tenant_key`),
  KEY `idx_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `policy_version` int(11) NOT NULL DEFAULT 1,
  `config_hash` char(64) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant` (`tenant_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `category_key` varchar(32) NOT NULL,
  `name` varchar(128) NOT NULL,
  `script_type` enum('src','inline') NOT NULL,
  `src_url` varchar(512) DEFAULT NULL,
  `inline_code` mediumtext DEFAULT NULL,
  `attributes_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes_json`)),
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_key`),
  KEY `idx_tenant_cat` (`tenant_key`,`category_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cmp_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmp_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `lang` varchar(8) NOT NULL,
  `text_key` varchar(64) NOT NULL,
  `text_value` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_lang_key` (`tenant_key`,`lang`,`text_key`),
  KEY `idx_tenant_lang` (`tenant_key`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_public_id` (`public_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_contact_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_verify_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verify_tokens` (
  `user_id` char(36) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `sent_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evidence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidence` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `case_id` char(36) NOT NULL,
  `kind` varchar(30) NOT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `hash_sha256` char(64) DEFAULT NULL,
  `captured_at` datetime DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_evidence_tenant_case` (`tenant_id`,`case_id`),
  KEY `ix_evidence_tenant_kind` (`tenant_id`,`kind`),
  KEY `fk_evidence_case` (`case_id`),
  CONSTRAINT `fk_evidence_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evidence_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lego_color_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lego_color_map` (
  `bl_color_id` int(11) NOT NULL,
  `rb_color_id` int(11) NOT NULL,
  PRIMARY KEY (`bl_color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lego_element_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lego_element_map` (
  `element_id` bigint(20) NOT NULL,
  `part_num` varchar(32) DEFAULT NULL,
  `rb_part_num` varchar(32) DEFAULT NULL,
  `rb_color_id` int(11) DEFAULT NULL,
  `bl_color_id` int(11) DEFAULT NULL,
  `color_name` varchar(64) DEFAULT NULL,
  `rb_name` varchar(255) DEFAULT NULL,
  `rb_part_img_url` text DEFAULT NULL,
  `rb_part_img_local` varchar(255) DEFAULT NULL,
  `rb_cached_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `rb_color_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lego_rb_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lego_rb_colors` (
  `rb_color_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rgb` char(6) DEFAULT NULL,
  `is_trans` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`rb_color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loss_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loss_cases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(64) NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `case_type` varchar(32) NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'open',
  `delta_g` int(11) DEFAULT NULL,
  `threshold_g` int(11) DEFAULT NULL,
  `severity` tinyint(4) NOT NULL DEFAULT 1,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loss_cases` (`tenant_id`,`order_id`,`case_type`),
  KEY `idx_loss_cases_tenant` (`tenant_id`),
  KEY `idx_loss_cases_order` (`order_id`),
  KEY `idx_loss_cases_type` (`case_type`),
  CONSTRAINT `fk_loss_cases_order` FOREIGN KEY (`order_id`) REFERENCES `loss_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loss_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loss_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(64) NOT NULL,
  `external_id` varchar(64) NOT NULL,
  `platform` varchar(32) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `gross_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ordered_at` datetime DEFAULT NULL,
  `customer_ref` varchar(64) DEFAULT NULL,
  `country_ship` char(2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loss_orders` (`tenant_id`,`platform`,`external_id`),
  KEY `idx_loss_orders_tenant` (`tenant_id`),
  KEY `idx_loss_orders_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loss_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loss_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(64) NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `return_weight_g` int(11) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loss_returns` (`tenant_id`,`order_id`),
  KEY `idx_loss_returns_tenant` (`tenant_id`),
  KEY `idx_loss_returns_order` (`order_id`),
  CONSTRAINT `fk_loss_returns_order` FOREIGN KEY (`order_id`) REFERENCES `loss_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loss_shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loss_shipments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(64) NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `packed_weight_g` int(11) NOT NULL,
  `box_id` varchar(64) DEFAULT NULL,
  `packed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loss_shipments` (`tenant_id`,`order_id`),
  KEY `idx_loss_shipments_tenant` (`tenant_id`),
  KEY `idx_loss_shipments_order` (`order_id`),
  CONSTRAINT `fk_loss_shipments_order` FOREIGN KEY (`order_id`) REFERENCES `loss_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loss_tenant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loss_tenant_settings` (
  `tenant_id` char(36) NOT NULL,
  `has_orders` tinyint(1) NOT NULL DEFAULT 1,
  `has_packed_weight` tinyint(1) NOT NULL DEFAULT 0,
  `has_return_weight` tinyint(1) NOT NULL DEFAULT 0,
  `has_shipments` tinyint(1) NOT NULL DEFAULT 0,
  `has_payments` tinyint(1) NOT NULL DEFAULT 0,
  `packed_missing_after_hours` int(11) NOT NULL DEFAULT 48,
  `packed_outlier_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `packed_outlier_low_ratio` decimal(6,3) NOT NULL DEFAULT 0.400,
  `packed_outlier_high_ratio` decimal(6,3) NOT NULL DEFAULT 2.500,
  `packed_outlier_min_median_g` int(11) NOT NULL DEFAULT 50,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `external_id` varchar(80) NOT NULL,
  `platform` varchar(40) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `gross_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `customer_ref` varchar(80) DEFAULT NULL,
  `country_ship` varchar(2) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'created',
  `ordered_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_orders_tenant_platform_external` (`tenant_id`,`platform`,`external_id`),
  KEY `ix_orders_tenant_created` (`tenant_id`,`created_at`),
  KEY `ix_orders_tenant_status` (`tenant_id`,`status`),
  KEY `ix_orders_tenant_ordered_at` (`tenant_id`,`ordered_at`),
  CONSTRAINT `fk_orders_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `provider` varchar(30) NOT NULL,
  `provider_ref` varchar(120) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'paid',
  `gross` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_payments_tenant_order` (`tenant_id`,`order_id`),
  KEY `ix_payments_tenant_paid_at` (`tenant_id`,`paid_at`),
  KEY `ix_payments_provider_ref` (`tenant_id`,`provider`,`provider_ref`),
  KEY `fk_payments_order` (`order_id`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_merge_dailystats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_merge_dailystats` (
  `stat_date` date NOT NULL,
  `uploads_ok` int(11) NOT NULL DEFAULT 0,
  `uploads_bytes` bigint(20) NOT NULL DEFAULT 0,
  `uploads_files` bigint(20) NOT NULL DEFAULT 0,
  `merges_started` int(11) NOT NULL DEFAULT 0,
  `merges_success` int(11) NOT NULL DEFAULT 0,
  `merges_error` int(11) NOT NULL DEFAULT 0,
  `merges_blocked` int(11) NOT NULL DEFAULT 0,
  `merges_input_bytes` bigint(20) NOT NULL DEFAULT 0,
  `merges_output_bytes` bigint(20) NOT NULL DEFAULT 0,
  `downloads` int(11) NOT NULL DEFAULT 0,
  `downloads_bytes` bigint(20) NOT NULL DEFAULT 0,
  `jobs_watermark` int(11) NOT NULL DEFAULT 0,
  `jobs_page_numbers` int(11) NOT NULL DEFAULT 0,
  `inputs_encrypted` int(11) NOT NULL DEFAULT 0,
  `inputs_heic` int(11) NOT NULL DEFAULT 0,
  `inputs_tiff` int(11) NOT NULL DEFAULT 0,
  `inputs_webp` int(11) NOT NULL DEFAULT 0,
  `quality_small` int(11) NOT NULL DEFAULT 0,
  `quality_normal` int(11) NOT NULL DEFAULT 0,
  `quality_best` int(11) NOT NULL DEFAULT 0,
  `tool_imagick` int(11) NOT NULL DEFAULT 0,
  `tool_magick` int(11) NOT NULL DEFAULT 0,
  `tool_mixed` int(11) NOT NULL DEFAULT 0,
  `tool_none` int(11) NOT NULL DEFAULT 0,
  `large_events` int(11) NOT NULL DEFAULT 0,
  `sum_output_ratio` double NOT NULL DEFAULT 0,
  `cnt_output_ratio` int(11) NOT NULL DEFAULT 0,
  `sum_total_ms` bigint(20) NOT NULL DEFAULT 0,
  `cnt_total_ms` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_merge_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_merge_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `job_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(64) NOT NULL,
  `stage` varchar(32) DEFAULT NULL,
  `tool` varchar(32) DEFAULT NULL,
  `ok` tinyint(1) NOT NULL DEFAULT 1,
  `bytes` bigint(20) unsigned DEFAULT NULL,
  `files_count` int(10) unsigned DEFAULT NULL,
  `ms` int(10) unsigned DEFAULT NULL,
  `meta_json` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pdf_merge_events_created` (`created_at`),
  KEY `idx_pdf_merge_events_event` (`event`,`created_at`),
  KEY `idx_pdf_merge_events_session` (`session_id`,`created_at`),
  KEY `idx_pdf_merge_events_job` (`job_id`,`created_at`),
  CONSTRAINT `fk_pdf_merge_events_job` FOREIGN KEY (`job_id`) REFERENCES `pdf_merge_jobs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pdf_merge_events_session` FOREIGN KEY (`session_id`) REFERENCES `pdf_merge_sessions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pdfmerge_events_dailystats
AFTER INSERT ON pdf_merge_events
FOR EACH ROW
BEGIN
  DECLARE d DATE;
  SET d = DATE(NEW.created_at);

  INSERT INTO pdf_merge_dailystats (stat_date)
  VALUES (d)
  ON DUPLICATE KEY UPDATE stat_date = stat_date;

  
  IF NEW.event = 'upload_batch' AND NEW.ok = 1 THEN
    UPDATE pdf_merge_dailystats
    SET
      uploads_ok = uploads_ok + 1,
      uploads_bytes = uploads_bytes + IFNULL(NEW.bytes, 0),
      uploads_files = uploads_files + IFNULL(NEW.files_count, 0)
    WHERE stat_date = d;
  END IF;

  
  IF NEW.event = 'merge_start' AND NEW.ok = 1 THEN
    UPDATE pdf_merge_dailystats
    SET
      merges_started = merges_started + 1
    WHERE stat_date = d;
  END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `pdf_merge_job_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_merge_job_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `ext` varchar(10) DEFAULT NULL,
  `kind` enum('pdf','image') NOT NULL DEFAULT 'pdf',
  `bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `converted_to_pdf` tinyint(1) NOT NULL DEFAULT 0,
  `convert_tool` enum('none','imagick','magick') NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`),
  KEY `idx_pdf_merge_job_files_job` (`job_id`,`created_at`),
  KEY `idx_pdf_merge_job_files_kind` (`kind`),
  CONSTRAINT `fk_pdf_merge_job_files_job` FOREIGN KEY (`job_id`) REFERENCES `pdf_merge_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_merge_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_merge_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `status` enum('started','success','error','blocked') NOT NULL DEFAULT 'started',
  `input_files_count` int(10) unsigned NOT NULL DEFAULT 0,
  `input_total_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `input_pdf_count` int(10) unsigned NOT NULL DEFAULT 0,
  `input_image_count` int(10) unsigned NOT NULL DEFAULT 0,
  `input_pages_total` int(10) unsigned DEFAULT NULL,
  `largest_input_file_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `has_heic` tinyint(1) NOT NULL DEFAULT 0,
  `has_tiff` tinyint(1) NOT NULL DEFAULT 0,
  `has_webp` tinyint(1) NOT NULL DEFAULT 0,
  `has_encrypted_pdf` tinyint(1) NOT NULL DEFAULT 0,
  `preset` enum('small','normal','best') NOT NULL DEFAULT 'normal',
  `dpi` smallint(5) unsigned NOT NULL DEFAULT 200,
  `autorotate` tinyint(1) NOT NULL DEFAULT 1,
  `page_numbers` tinyint(1) NOT NULL DEFAULT 0,
  `page_numbers_pos` varchar(16) DEFAULT NULL,
  `page_number_fmt` varchar(16) DEFAULT NULL,
  `watermark` tinyint(1) NOT NULL DEFAULT 0,
  `watermark_len` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `watermark_angle` smallint(6) DEFAULT NULL,
  `watermark_opacity` decimal(4,3) DEFAULT NULL,
  `optimize_pdf` tinyint(1) NOT NULL DEFAULT 1,
  `gs_pdfsettings` varchar(16) DEFAULT NULL,
  `convert_tool_used` enum('none','imagick','magick','mixed') NOT NULL DEFAULT 'none',
  `output_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `output_ratio` decimal(7,4) DEFAULT NULL,
  `output_pages` int(10) unsigned DEFAULT NULL,
  `upload_ms` int(10) unsigned DEFAULT NULL,
  `convert_ms` int(10) unsigned DEFAULT NULL,
  `merge_ms` int(10) unsigned DEFAULT NULL,
  `total_ms` int(10) unsigned DEFAULT NULL,
  `downloaded` tinyint(1) NOT NULL DEFAULT 0,
  `downloaded_at` datetime DEFAULT NULL,
  `is_large_event` tinyint(1) NOT NULL DEFAULT 0,
  `large_reason` varchar(64) DEFAULT NULL,
  `error_code` varchar(64) DEFAULT NULL,
  `error_stage` enum('upload','convert','merge','download','limits','unknown') DEFAULT NULL,
  `error_tool` enum('imagick','ghostscript','magick','php','unknown') DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `meta_json` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pdf_merge_jobs_created` (`created_at`),
  KEY `idx_pdf_merge_jobs_session` (`session_id`,`created_at`),
  KEY `idx_pdf_merge_jobs_status` (`status`,`created_at`),
  KEY `idx_pdf_merge_jobs_large` (`is_large_event`,`created_at`),
  KEY `idx_pdf_merge_jobs_downloaded` (`downloaded`,`created_at`),
  CONSTRAINT `fk_pdf_merge_jobs_session` FOREIGN KEY (`session_id`) REFERENCES `pdf_merge_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pdfmerge_jobs_dailystats
AFTER UPDATE ON pdf_merge_jobs
FOR EACH ROW
BEGIN
  DECLARE d DATE;
  SET d = DATE(COALESCE(NEW.finished_at, NEW.created_at));

  INSERT INTO pdf_merge_dailystats (stat_date)
  VALUES (d)
  ON DUPLICATE KEY UPDATE stat_date = stat_date;

  
  IF OLD.status <> NEW.status THEN

    IF NEW.status = 'success' THEN
      UPDATE pdf_merge_dailystats
      SET
        merges_success = merges_success + 1,
        merges_input_bytes = merges_input_bytes + IFNULL(NEW.input_total_bytes, 0),
        merges_output_bytes = merges_output_bytes + IFNULL(NEW.output_bytes, 0),

        jobs_watermark = jobs_watermark + IF(NEW.watermark = 1, 1, 0),
        jobs_page_numbers = jobs_page_numbers + IF(NEW.page_numbers = 1, 1, 0),

        inputs_encrypted = inputs_encrypted + IF(NEW.has_encrypted_pdf = 1, 1, 0),
        inputs_heic = inputs_heic + IF(NEW.has_heic = 1, 1, 0),
        inputs_tiff = inputs_tiff + IF(NEW.has_tiff = 1, 1, 0),
        inputs_webp = inputs_webp + IF(NEW.has_webp = 1, 1, 0),

        quality_small  = quality_small  + IF(NEW.preset = 'small', 1, 0),
        quality_normal = quality_normal + IF(NEW.preset = 'normal', 1, 0),
        quality_best   = quality_best   + IF(NEW.preset = 'best', 1, 0),

        tool_imagick = tool_imagick + IF(NEW.convert_tool_used = 'imagick', 1, 0),
        tool_magick  = tool_magick  + IF(NEW.convert_tool_used = 'magick', 1, 0),
        tool_mixed   = tool_mixed   + IF(NEW.convert_tool_used = 'mixed', 1, 0),
        tool_none    = tool_none    + IF(NEW.convert_tool_used = 'none', 1, 0),

        large_events = large_events + IF(NEW.is_large_event = 1, 1, 0),

        sum_output_ratio = sum_output_ratio + IFNULL(NEW.output_ratio, 0),
        cnt_output_ratio = cnt_output_ratio + IF(NEW.output_ratio IS NULL, 0, 1),

        sum_total_ms = sum_total_ms + IFNULL(NEW.total_ms, 0),
        cnt_total_ms = cnt_total_ms + IF(NEW.total_ms IS NULL, 0, 1)

      WHERE stat_date = d;

    ELSEIF NEW.status = 'error' THEN
      UPDATE pdf_merge_dailystats
      SET merges_error = merges_error + 1
      WHERE stat_date = d;

    ELSEIF NEW.status = 'blocked' THEN
      UPDATE pdf_merge_dailystats
      SET merges_blocked = merges_blocked + 1
      WHERE stat_date = d;

    END IF;

  END IF;

  
  IF IFNULL(OLD.downloaded, 0) <> 1 AND IFNULL(NEW.downloaded, 0) = 1 THEN
    UPDATE pdf_merge_dailystats
    SET
      downloads = downloads + 1,
      downloads_bytes = downloads_bytes + IFNULL(NEW.output_bytes, 0)
    WHERE stat_date = d;
  END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `pdf_merge_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_merge_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `guest_token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `ua_hash` char(64) NOT NULL,
  `country_code` char(2) DEFAULT NULL,
  `region_code` varchar(8) DEFAULT NULL,
  `asn` int(10) unsigned DEFAULT NULL,
  `as_org` varchar(128) DEFAULT NULL,
  `first_referrer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pdf_merge_sessions_token` (`guest_token`),
  KEY `idx_pdf_merge_sessions_created_at` (`created_at`),
  KEY `idx_pdf_merge_sessions_last_seen` (`last_seen_at`),
  KEY `idx_pdf_merge_sessions_country` (`country_code`),
  KEY `idx_pdf_merge_sessions_asn` (`asn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plan_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_key` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_key` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) DEFAULT NULL,
  `trial_days` int(11) NOT NULL DEFAULT 14,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_key` (`plan_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `returns` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `return_weight_g` int(11) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `carrier` varchar(30) DEFAULT NULL,
  `tracking_code` varchar(80) DEFAULT NULL,
  `video_uri` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_returns_one_per_order` (`tenant_id`,`order_id`),
  KEY `ix_returns_tenant_returned_at` (`tenant_id`,`returned_at`),
  KEY `ix_returns_tracking` (`tenant_id`,`carrier`,`tracking_code`),
  KEY `fk_returns_order` (`order_id`),
  CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_returns_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipments` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `packed_weight_g` int(11) DEFAULT NULL,
  `box_id` varchar(60) DEFAULT NULL,
  `packed_at` datetime DEFAULT NULL,
  `carrier` varchar(30) DEFAULT NULL,
  `tracking_code` varchar(80) DEFAULT NULL,
  `video_uri` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_shipments_one_per_order` (`tenant_id`,`order_id`),
  KEY `ix_shipments_tenant_packed_at` (`tenant_id`,`packed_at`),
  KEY `ix_shipments_tracking` (`tenant_id`,`carrier`,`tracking_code`),
  KEY `fk_shipments_order` (`order_id`),
  CONSTRAINT `fk_shipments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_shipments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shipping_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `carrier` varchar(50) NOT NULL,
  `service` varchar(80) NOT NULL,
  `zone` varchar(50) NOT NULL,
  `weight_from_g` int(10) unsigned NOT NULL,
  `weight_to_g` int(10) unsigned NOT NULL,
  `price_ex` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `handling_ex` decimal(10,2) NOT NULL DEFAULT 0.00,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `source_label` varchar(120) DEFAULT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate` (`carrier`,`service`,`zone`,`weight_from_g`,`weight_to_g`,`currency`,`valid_from`,`valid_to`),
  KEY `idx_active` (`active`),
  KEY `idx_lookup` (`carrier`,`zone`,`weight_from_g`,`weight_to_g`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shipping_surcharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_surcharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `carrier` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL DEFAULT '0',
  `service` varchar(80) NOT NULL DEFAULT '',
  `zone` varchar(50) NOT NULL DEFAULT '',
  `amount_ex` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `rule_text` varchar(500) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 100,
  `source_label` varchar(120) NOT NULL DEFAULT '',
  `source_url` varchar(255) NOT NULL DEFAULT '',
  `valid_from` date NOT NULL DEFAULT '0000-00-00',
  `valid_to` date NOT NULL DEFAULT '0000-00-00',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_surcharge` (`carrier`,`name`,`service`,`zone`,`currency`,`valid_from`,`valid_to`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tenant_api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_api_keys` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `name` varchar(80) NOT NULL,
  `key_hash` varchar(255) NOT NULL,
  `scopes` varchar(255) NOT NULL DEFAULT 'import:orders,import:payments',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_api_keys_tenant_name` (`tenant_id`,`name`),
  KEY `ix_api_keys_tenant` (`tenant_id`),
  CONSTRAINT `fk_api_keys_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tenant_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_key` varchar(64) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('trial','active','canceled','expired') NOT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `current_period_ends_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `tenant_subscriptions_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` char(36) NOT NULL,
  `name` varchar(120) NOT NULL,
  `plan` varchar(20) NOT NULL DEFAULT 'free',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_tenants_plan` (`plan`),
  KEY `ix_tenants_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tool_diff_saves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tool_diff_saves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `title` varchar(120) NOT NULL DEFAULT '',
  `left_text` mediumtext NOT NULL,
  `right_text` mediumtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id_created` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tool_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tool_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tool` varchar(50) NOT NULL,
  `event` varchar(50) NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tool_event_created` (`tool`,`event`,`created_at`),
  KEY `idx_session_created` (`session_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_acquisition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_acquisition` (
  `user_id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `first_seen_at` datetime DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `landing_url` varchar(2048) DEFAULT NULL,
  `referrer_url` varchar(2048) DEFAULT NULL,
  `utm_source` varchar(120) DEFAULT NULL,
  `utm_medium` varchar(120) DEFAULT NULL,
  `utm_campaign` varchar(200) DEFAULT NULL,
  `utm_term` varchar(200) DEFAULT NULL,
  `utm_content` varchar(200) DEFAULT NULL,
  `gclid` varchar(120) DEFAULT NULL,
  `fbclid` varchar(120) DEFAULT NULL,
  `last_landing_url` varchar(2048) DEFAULT NULL,
  `last_referrer_url` varchar(2048) DEFAULT NULL,
  `last_utm_source` varchar(120) DEFAULT NULL,
  `last_utm_medium` varchar(120) DEFAULT NULL,
  `last_utm_campaign` varchar(200) DEFAULT NULL,
  `last_utm_term` varchar(200) DEFAULT NULL,
  `last_utm_content` varchar(200) DEFAULT NULL,
  `last_gclid` varchar(120) DEFAULT NULL,
  `last_fbclid` varchar(120) DEFAULT NULL,
  `registered_from_tool` varchar(120) DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `ua_hash` char(64) DEFAULT NULL,
  `country_code` char(2) DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  KEY `idx_tenant_registered` (`tenant_id`,`registered_at`),
  KEY `idx_utm_campaign` (`utm_campaign`),
  KEY `idx_referrer_prefix` (`referrer_url`(191)),
  KEY `idx_landing_prefix` (`landing_url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `page_url` varchar(2048) DEFAULT NULL,
  `meta_json` longtext DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `ua_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `country_code` char(2) DEFAULT NULL,
  `region_code` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_event_dt` (`tenant_id`,`event`,`created_at`),
  KEY `idx_user_dt` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `user_id` char(36) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session` (`session_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_twofa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_twofa` (
  `user_id` char(36) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `secret_enc` varbinary(255) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `last_used_counter` bigint(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_twofa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_twofa_backup_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_twofa_backup_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_backup_user` (`user_id`),
  CONSTRAINT `fk_backup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_twofa_trusted_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_twofa_trusted_devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `selector` char(12) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_selector` (`selector`),
  KEY `ix_user` (`user_id`),
  KEY `ix_exp` (`expires_at`),
  CONSTRAINT `fk_trusted_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `tenant_id` char(36) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `email_verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_users_tenant_email` (`tenant_id`,`email`),
  KEY `ix_users_tenant_id` (`tenant_id`),
  KEY `ix_users_active` (`is_active`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `bg_cmp_clone_tenant` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `bg_cmp_clone_tenant`()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_table VARCHAR(255);

  DECLARE cur CURSOR FOR
    SELECT t.table_name
    FROM information_schema.tables t
    JOIN information_schema.columns c
      ON c.table_schema = DATABASE()
     AND c.table_name = t.table_name
     AND c.column_name = 'tenant_key'
    WHERE t.table_schema = DATABASE()
      AND t.table_name LIKE 'cmp\_%'
      AND t.table_type = 'BASE TABLE'
      
      AND t.table_name NOT IN ('cmp_domains','cmp_consents','cmp_consent_logs');

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO v_table;
    IF done = 1 THEN
      LEAVE read_loop;
    END IF;

    
    
    SET @cols := NULL;
    SELECT GROUP_CONCAT(CONCAT('`', column_name, '`') ORDER BY ordinal_position SEPARATOR ', ')
      INTO @cols
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = v_table
      AND NOT (extra LIKE '%auto_increment%');  

    
    SET @sel := NULL;

    SELECT GROUP_CONCAT(
      CASE
        WHEN column_name = 'tenant_key' THEN CONCAT("'", REPLACE(@NEW_TENANT, "'", "''"), "'")
        WHEN column_name = 'domain_id' THEN "m.new_id"
        ELSE CONCAT("t.`", column_name, "`")
      END
      ORDER BY ordinal_position SEPARATOR ', '
    )
    INTO @sel
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = v_table
      AND NOT (extra LIKE '%auto_increment%');

    
    SET @has_domain_id := 0;
    SELECT COUNT(*) INTO @has_domain_id
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = v_table
      AND column_name = 'domain_id';

    
    IF @has_domain_id > 0 THEN
      SET @sql := CONCAT(
        "INSERT IGNORE INTO `", v_table, "` (", @cols, ") ",
        "SELECT ", @sel, " ",
        "FROM `", v_table, "` t ",
        "JOIN tmp_cmp_domain_map m ON m.old_id = t.domain_id ",
        "WHERE t.tenant_key = '", REPLACE(@OLD_TENANT, "'", "''"), "'"
      );
    ELSE
      SET @sql := CONCAT(
        "INSERT IGNORE INTO `", v_table, "` (", @cols, ") ",
        "SELECT ", @sel, " ",
        "FROM `", v_table, "` t ",
        "WHERE t.tenant_key = '", REPLACE(@OLD_TENANT, "'", "''"), "'"
      );
    END IF;

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

  END LOOP;

  CLOSE cur;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

