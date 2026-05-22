<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AccessGuard Vulnerability Radar — foundation tables.
 *
 * Five tables that model the full scan → match → risk-score pipeline:
 *   - radar_assets             what the customer runs (site, server, plugin, …)
 *   - radar_software_instances which software+version sits on each asset
 *   - radar_vulnerabilities    CVE catalog pulled from OSV/NVD/CISA KEV (tenant-agnostic)
 *   - radar_findings           asset × software × vulnerability with workflow state
 *   - radar_scans              audit trail of detection runs
 *
 * Critical findings (risk_score >= 80) additionally spawn a RiskFlag row with
 * kind='vulnerability', so they pipe into AccessGuard's existing risk dashboard
 * and instant-alert notification path.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::create('accessguard_radar_assets', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('system_id')->nullable();
			$t->string('kind', 30);
			// website | webshop | cms | plugin | server | endpoint | saas_app | api | container | network_device
			$t->string('name', 200);
			$t->string('hostname', 255)->nullable();
			$t->string('url', 500)->nullable();
			$t->string('vendor', 120)->nullable();
			$t->string('product', 120)->nullable();
			$t->string('current_version', 80)->nullable();
			$t->string('environment', 20)->default('production');
			// production | staging | test
			$t->string('is_public', 10)->default('unknown');
			// yes | no | unknown
			$t->string('auth_required', 10)->default('unknown');
			$t->string('criticality', 10)->default('medium');
			// low | medium | high | critical
			$t->string('status', 20)->default('active');
			// active | ignored | deleted | unknown
			$t->timestamp('discovered_at')->nullable();
			$t->timestamp('last_seen_at')->nullable();
			$t->timestamp('last_scanned_at')->nullable();
			$t->char('owner_user_id', 36)->nullable();
			$t->text('notes')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'status'], 'agra_t_status_idx');
			$t->index(['tenant_id', 'kind'], 'agra_t_kind_idx');
			$t->index(['tenant_id', 'criticality'], 'agra_t_crit_idx');
			$t->foreign('system_id')->references('id')->on('accessguard_systems')->nullOnDelete();
		});

		Schema::create('accessguard_radar_software_instances', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('asset_id');
			$t->string('vendor', 120);
			$t->string('product', 120);
			$t->string('version', 80)->nullable();
			$t->string('detection_method', 40);
			// http_header | generator_meta | wp_json | tls_banner | osv_lookup | manual | agent
			$t->string('path', 255)->nullable();
			$t->timestamp('first_detected_at')->nullable();
			$t->timestamp('last_seen_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'asset_id'], 'agrsi_t_asset_idx');
			$t->index(['vendor', 'product', 'version'], 'agrsi_vpv_idx');
			// A single (asset, product, path) can only exist once; version updates in-place.
			$t->unique(['asset_id', 'vendor', 'product', 'path'], 'agrsi_avpp_uq');
			$t->foreign('asset_id')->references('id')->on('accessguard_radar_assets')->cascadeOnDelete();
		});

		Schema::create('accessguard_radar_vulnerabilities', function (Blueprint $t) {
			$t->id();
			$t->string('source', 30);
			// osv | nvd | cisa_kev | ghsa | wpvuln | vendor
			$t->string('source_id', 120);
			$t->string('cve_id', 30)->nullable();
			$t->string('vendor', 120);
			$t->string('product', 120);
			$t->string('affected_range', 200);
			// semver range e.g. ">=1.0.0 <1.2.5"
			$t->string('fixed_version', 80)->nullable();
			$t->decimal('cvss_score', 3, 1)->nullable();
			$t->string('severity', 10);
			// low | medium | high | critical
			$t->string('impact_category', 30)->default('unknown');
			// rce | privesc | auth_bypass | data_leak | sqli | xss | ssrf | dos | supply_chain | unknown
			$t->boolean('exploit_available')->default(false);
			$t->boolean('actively_exploited')->default(false);
			$t->boolean('cisa_kev')->default(false);
			$t->boolean('patch_available')->default(false);
			$t->date('published_at')->nullable();
			$t->date('updated_at_source')->nullable();
			$t->text('description');
			$t->json('references_json')->nullable();
			$t->timestamps();

			$t->index(['vendor', 'product'], 'agrv_vp_idx');
			$t->index(['cisa_kev'], 'agrv_kev_idx');
			$t->index(['severity'], 'agrv_sev_idx');
			$t->unique(['source', 'source_id'], 'agrv_source_uq');
		});

		Schema::create('accessguard_radar_findings', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('asset_id');
			$t->unsignedBigInteger('software_instance_id');
			$t->unsignedBigInteger('vulnerability_id');
			$t->integer('risk_score');
			// 0-100
			$t->json('risk_factors');
			$t->string('severity', 10);
			// derived from risk_score for fast filtering: low(<=20) | medium(21-50) | high(51-80) | critical(81+)
			$t->string('status', 20)->default('new');
			// new | confirmed | accepted_risk | planned | in_progress | patched |
			// mitigated | ignored | false_positive | resolved | reopened
			$t->char('assigned_to_user_id', 36)->nullable();
			$t->date('due_date')->nullable();
			$t->char('accepted_by_user_id', 36)->nullable();
			$t->date('accepted_until')->nullable();
			$t->text('resolution_notes')->nullable();
			$t->timestamp('last_verified_at')->nullable();
			$t->string('proof_of_fix', 500)->nullable();
			$t->string('linked_ticket_id', 80)->nullable();
			$t->unsignedBigInteger('risk_flag_id')->nullable();
			// set when the finding was elevated into a RiskFlag row (severity >= 80)
			$t->char('ai_explanation_cache_key', 64)->nullable();
			$t->timestamp('first_detected_at')->useCurrent();
			$t->timestamp('last_detected_at')->useCurrent();
			$t->timestamp('resolved_at')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'severity', 'status'], 'agrf_tss_idx');
			$t->index(['tenant_id', 'assigned_to_user_id'], 'agrf_ta_idx');
			$t->index(['tenant_id', 'due_date'], 'agrf_tdd_idx');
			$t->unique(['asset_id', 'software_instance_id', 'vulnerability_id'], 'agrf_asv_uq');
			$t->foreign('asset_id')->references('id')->on('accessguard_radar_assets')->cascadeOnDelete();
			$t->foreign('software_instance_id')->references('id')->on('accessguard_radar_software_instances')->cascadeOnDelete();
			$t->foreign('vulnerability_id')->references('id')->on('accessguard_radar_vulnerabilities')->cascadeOnDelete();
			$t->foreign('risk_flag_id')->references('id')->on('accessguard_risk_flags')->nullOnDelete();
		});

		Schema::create('accessguard_radar_scans', function (Blueprint $t) {
			$t->id();
			$t->char('tenant_id', 36);
			$t->unsignedBigInteger('asset_id');
			$t->string('scan_type', 30);
			// http_probe | agent_pull | feed_match | manual
			$t->string('status', 20)->default('queued');
			// queued | running | success | error | skipped
			$t->integer('detections_count')->default(0);
			$t->integer('findings_count')->default(0);
			$t->timestamp('started_at')->useCurrent();
			$t->timestamp('finished_at')->nullable();
			$t->text('error_message')->nullable();
			$t->json('raw_output')->nullable();
			$t->timestamps();

			$t->index(['tenant_id', 'asset_id', 'started_at'], 'agrs_tas_idx');
			$t->foreign('asset_id')->references('id')->on('accessguard_radar_assets')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('accessguard_radar_scans');
		Schema::dropIfExists('accessguard_radar_findings');
		Schema::dropIfExists('accessguard_radar_vulnerabilities');
		Schema::dropIfExists('accessguard_radar_software_instances');
		Schema::dropIfExists('accessguard_radar_assets');
	}
};
