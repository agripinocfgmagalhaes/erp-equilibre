<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE lancamentos_bancarios MODIFY origem ENUM('manual','conta_pagar','conta_receber','transferencia') NOT NULL DEFAULT 'manual'");
        Schema::table('lancamentos_bancarios', function (Blueprint $table) {
            $table->string('transferencia_grupo', 36)->nullable()->after('origem_id');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos_bancarios', function (Blueprint $table) {
            $table->dropColumn('transferencia_grupo');
        });
        DB::statement("ALTER TABLE lancamentos_bancarios MODIFY origem ENUM('manual','conta_pagar','conta_receber') NOT NULL DEFAULT 'manual'");
    }
};
