<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		DB::statement('ALTER TABLE user_twofa MODIFY secret_enc BLOB NULL');
	}

	public function down(): void
	{
		DB::statement('ALTER TABLE user_twofa MODIFY secret_enc VARBINARY(255) NULL');
	}
};
