<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE folha_fechamentos MODIFY dias_trabalhados DECIMAL(5,1) NOT NULL DEFAULT 0');
    }
    public function down(): void
    {
        DB::statement('ALTER TABLE folha_fechamentos MODIFY dias_trabalhados SMALLINT UNSIGNED NOT NULL DEFAULT 0');
    }
};
