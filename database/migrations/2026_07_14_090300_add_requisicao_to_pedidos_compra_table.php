<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->foreignId('requisicao_compra_id')->nullable()->after('id')->constrained('requisicoes_compra')->nullOnDelete();
            $table->foreignId('cotacao_compra_id')->nullable()->after('requisicao_compra_id')->constrained('cotacoes_compra')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cotacao_compra_id');
            $table->dropConstrainedForeignId('requisicao_compra_id');
        });
    }
};
