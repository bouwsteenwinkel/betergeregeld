<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Een bureau-beheerder (role='agency') hoort bij een agency, niet bij een tenant.
 * Daarom mag users.tenant_id nu NULL zijn. Raw MODIFY behoudt de bestaande
 * foreign key (nullable botst niet met de FK).
 */
return new class extends Migration {
	public function up(): void
	{
		DB::statement('ALTER TABLE users MODIFY tenant_id CHAR(36) NULL');
	}

	public function down(): void
	{
		DB::statement('ALTER TABLE users MODIFY tenant_id CHAR(36) NOT NULL');
	}
};
