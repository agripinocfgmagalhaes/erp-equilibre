<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $type = DB::select("SHOW COLUMNS FROM unidades LIKE 'status'")[0]->Type ?? '';
        if (str_starts_with($type, 'enum') && ! str_contains($type, 'indisponivel')) {
            DB::statement("ALTER TABLE unidades MODIFY status ENUM('disponivel','reservado','vendido','distratado','indisponivel') NOT NULL DEFAULT 'disponivel'");
        }
    }

    public function down(): void {}
};
