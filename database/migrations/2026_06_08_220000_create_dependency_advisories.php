<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security fase 3: resultaten van `composer audit` / `npm audit` op de eigen
 * code-projecten (platform-niveau, niet per klantsite). Bij elke run ververst.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create('dependency_advisories', function (Blueprint $t) {
			$t->id();
			$t->enum('ecosystem', ['composer', 'npm']);
			$t->string('project', 120);
			$t->string('package', 190);
			$t->string('severity', 16)->nullable();
			$t->string('title', 255);
			$t->string('advisory_id', 64)->nullable();
			$t->string('cve', 32)->nullable();
			$t->string('fixed_in', 40)->nullable();
			$t->string('link', 500)->nullable();
			$t->timestamp('imported_at')->nullable();

			$t->index(['ecosystem', 'project'], 'depadv_eco_project_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('dependency_advisories');
	}
};
