<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Limpa dados inválidos antes de alterar tipos
        DB::table('unidades')->delete();

        // andar: int -> varchar
        $type = DB::select("SHOW COLUMNS FROM unidades LIKE 'andar'")[0]->Type ?? '';
        if (str_starts_with($type, 'int')) {
            DB::statement("ALTER TABLE unidades MODIFY andar VARCHAR(100) NULL");
        }

        // tipo: enum -> varchar
        $type = DB::select("SHOW COLUMNS FROM unidades LIKE 'tipo'")[0]->Type ?? '';
        if (str_starts_with($type, 'enum')) {
            DB::statement("ALTER TABLE unidades MODIFY tipo VARCHAR(100) NULL");
        }

        // tipologia: garante varchar(100)
        DB::statement("ALTER TABLE unidades MODIFY tipologia VARCHAR(100) NULL");

        // vaga_garagem: garante varchar(50)
        DB::statement("ALTER TABLE unidades MODIFY vaga_garagem VARCHAR(50) NULL");
    }

    public function down(): void {
        // Não reverte (perderia dados)
    }
};
