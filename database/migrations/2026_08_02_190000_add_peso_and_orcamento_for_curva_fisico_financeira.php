<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('fases_obra', function (Blueprint $table) {
            if (! Schema::hasColumn('fases_obra', 'peso')) {
                $table->decimal('peso', 5, 2)->default(0)->after('percentual');
            }
        });
        Schema::table('projetos', function (Blueprint $table) {
            if (! Schema::hasColumn('projetos', 'valor_orcamento')) {
                $table->decimal('valor_orcamento', 15, 2)->nullable()->after('data_previsao_fim');
            }
        });
    }
    public function down(): void {
        Schema::table('fases_obra', function (Blueprint $table) {
            if (Schema::hasColumn('fases_obra', 'peso')) $table->dropColumn('peso');
        });
        Schema::table('projetos', function (Blueprint $table) {
            if (Schema::hasColumn('projetos', 'valor_orcamento')) $table->dropColumn('valor_orcamento');
        });
    }
};
