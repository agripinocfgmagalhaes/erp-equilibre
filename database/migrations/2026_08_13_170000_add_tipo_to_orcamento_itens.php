<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (! DB::getSchemaBuilder()->hasColumn('orcamento_itens', 'tipo')) {
            DB::statement("ALTER TABLE orcamento_itens ADD COLUMN tipo VARCHAR(20) NULL DEFAULT 'material' AFTER fase_padrao_id");
        }
    }
    public function down(): void {}
};
