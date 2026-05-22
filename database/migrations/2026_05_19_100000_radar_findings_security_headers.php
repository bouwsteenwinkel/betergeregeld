<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AccessGuard Radar — extend findings for non-CVE checks.
 *
 * Tot nu was elke Finding gekoppeld aan (asset × software × vulnerability)
 * = NVD/OSV/CISA-KEV record. Voor security-header-checks (en straks TLS,
 * cookies, etc.) is er geen CVE — we vinden een AFWEZIGE of zwakke header
 * op de asset zelf. Daarom:
 *
 *  - software_instance_id en vulnerability_id worden nullable
 *  - check_type onderscheidt findings:
 *      cve             — klassieke vulnerability-match (verplicht
 *                        software_instance_id + vulnerability_id)
 *      security_headers — http-headers-check op asset.url
 *      tls             — toekomst: cert + cipher-suite-scan
 *      cookies         — toekomst: secure/httponly/samesite
 *      cmp             — toekomst: cookie-banner-implementation
 *  - title + detail vullen we vrij voor non-CVE findings (CVE's hebben
 *    al een Vulnerability-record met cve_id + summary)
 *
 *  Backward-compat: bestaande rows krijgen check_type='cve' default.
 */
return new class extends Migration {
	public function up(): void
	{
		Schema::table('accessguard_radar_findings', function (Blueprint $t) {
			// Nullable maken zodat security-header-findings geen software/vuln
			// referenties hoeven te hebben
			$t->unsignedBigInteger('software_instance_id')->nullable()->change();
			$t->unsignedBigInteger('vulnerability_id')->nullable()->change();

			// Type-classificatie
			$t->string('check_type', 30)->default('cve')->after('vulnerability_id');
			// cve | security_headers | tls | cookies | cmp

			// Vrije titel + detail voor non-CVE findings
			$t->string('title', 200)->nullable()->after('check_type');
			$t->text('detail')->nullable()->after('title');

			// Vingerafdruk om dubbele findings te dedupliceren binnen (asset, check_type)
			// bv. 'missing_hsts', 'weak_csp', 'exposed_xpoweredby'
			$t->string('finding_key', 80)->nullable()->after('detail');

			$t->index(['tenant_id', 'check_type', 'status'], 'agrf_tcs_idx');
			$t->index(['asset_id', 'check_type', 'finding_key'], 'agrf_ack_idx');
		});
	}

	public function down(): void
	{
		Schema::table('accessguard_radar_findings', function (Blueprint $t) {
			$t->dropIndex('agrf_tcs_idx');
			$t->dropIndex('agrf_ack_idx');
			$t->dropColumn(['check_type', 'title', 'detail', 'finding_key']);
			// Note: niet terug-zetten naar NOT NULL — er kunnen security-header-
			// findings inmiddels bestaan met NULL vuln/software.
		});
	}
};
