<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('unidades', 'valor_avaliado')) {
                $table->decimal('valor_avaliado', 15, 2)->nullable()->after('valor_tabela');
            }
            if (! Schema::hasColumn('unidades', 'andar')) {
                $table->string('andar', 50)->nullable()->after('valor_avaliado');
            }
            if (! Schema::hasColumn('unidades', 'tipologia')) {
                $table->string('tipologia', 50)->nullable()->after('andar');
            }
            if (! Schema::hasColumn('unidades', 'vaga_garagem')) {
                $table->string('vaga_garagem', 20)->nullable()->after('tipologia');
            }
        });
    }

    public function down(): void {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn(['valor_avaliado', 'andar', 'tipologia', 'vaga_garagem']);
        });
    }
};
