<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('fases_padrao', 'ordem')) {
            Schema::table('fases_padrao', function (Blueprint $table) {
                $table->integer('ordem')->default(0)->after('macro_categoria');
            });
        }
    }
    public function down(): void {
        Schema::table('fases_padrao', function (Blueprint $table) {
            $table->dropColumn('ordem');
        });
    }
};
