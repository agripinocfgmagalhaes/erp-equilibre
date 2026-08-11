<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        $type = DB::select("SHOW COLUMNS FROM unidades LIKE 'status'")[0]->Type ?? '';
        if (str_contains($type, 'distratado')) {
            DB::statement("ALTER TABLE unidades MODIFY status ENUM('disponivel','reservado','vendido','indisponivel') NOT NULL DEFAULT 'disponivel'");
        }
    }

    public function down(): void {}
};
