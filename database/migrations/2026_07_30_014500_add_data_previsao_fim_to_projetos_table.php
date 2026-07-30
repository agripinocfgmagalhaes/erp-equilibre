<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('projetos', function (Blueprint $table) {
            if (! Schema::hasColumn('projetos', 'data_inicio')) {
                $table->date('data_inicio')->nullable();
            }
            if (! Schema::hasColumn('projetos', 'data_previsao_fim')) {
                $table->date('data_previsao_fim')->nullable();
            }
        });
    }
    public function down(): void {
        Schema::table('projetos', function (Blueprint $table) {
            if (Schema::hasColumn('projetos', 'data_previsao_fim')) {
                $table->dropColumn('data_previsao_fim');
            }
            if (Schema::hasColumn('projetos', 'data_inicio')) {
                $table->dropColumn('data_inicio');
            }
        });
    }
};
