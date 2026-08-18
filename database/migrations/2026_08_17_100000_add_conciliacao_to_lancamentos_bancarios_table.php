<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE lancamentos_bancarios MODIFY COLUMN origem ENUM('manual','conta_pagar','conta_receber','transferencia','extrato_inter') DEFAULT 'manual'");
        Schema::table('lancamentos_bancarios', function (Blueprint $table) {
            $table->boolean('conciliado')->default(false)->after('observacoes');
            $table->timestamp('conciliado_em')->nullable();
            $table->foreignId('conciliado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('inter_transacao_id')->nullable()->index();
        });
    }
    public function down(): void {
        Schema::table('lancamentos_bancarios', function (Blueprint $table) {
            $table->dropColumn(['conciliado','conciliado_em','conciliado_por','inter_transacao_id']);
        });
        DB::statement("ALTER TABLE lancamentos_bancarios MODIFY COLUMN origem ENUM('manual','conta_pagar','conta_receber','transferencia') DEFAULT 'manual'");
    }
};
