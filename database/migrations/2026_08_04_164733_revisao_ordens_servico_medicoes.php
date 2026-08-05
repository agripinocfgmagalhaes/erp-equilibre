<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (! Schema::hasColumn('contas_pagar', 'ordem_servico_id')) {
                $table->foreignId('ordem_servico_id')->nullable()->after('pedido_compra_id')->constrained('ordens_servico')->onDelete('set null');
            }
        });
        DB::table('medicoes')->where('status', 'rascunho')->update(['status' => 'medida']);
        DB::statement("ALTER TABLE medicoes MODIFY status ENUM('medida','aprovada','faturada','paga') NOT NULL DEFAULT 'medida'");
    }
    public function down(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (Schema::hasColumn('contas_pagar', 'ordem_servico_id')) { $table->dropConstrainedForeignId('ordem_servico_id'); }
        });
        DB::statement("ALTER TABLE medicoes MODIFY status ENUM('rascunho','medida','aprovada','faturada','paga') NOT NULL DEFAULT 'rascunho'");
    }
};
