<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('requisicoes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->nullOnDelete();
            $table->foreignId('fase_obra_id')->nullable()->constrained('fases_obra')->nullOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aprovador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'rascunho',
                'pendente_aprovacao',
                'reprovada',
                'em_cotacao',
                'cotada',
                'pedido_gerado',
                'cancelada',
            ])->default('rascunho');
            $table->date('data_requisicao');
            $table->text('justificativa')->nullable();
            $table->text('motivo_reprovacao')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        Schema::create('itens_requisicao_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicao_compra_id')->constrained('requisicoes_compra')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->string('descricao', 200);
            $table->string('unidade', 10)->default('UN');
            $table->decimal('quantidade', 12, 2)->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('itens_requisicao_compra');
        Schema::dropIfExists('requisicoes_compra');
    }
};
