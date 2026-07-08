<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->nullOnDelete();
            $table->foreignId('fase_obra_id')->nullable()->constrained('fases_obra')->nullOnDelete();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->cascadeOnDelete();
            $table->enum('status', ['rascunho','aprovado','recebido_parcial','recebido','cancelado'])->default('rascunho');
            $table->date('data_pedido');
            $table->date('data_previsao_entrega')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique()->nullable();
            $table->string('nome', 150);
            $table->string('unidade', 10)->default('UN');
            $table->string('categoria', 100)->nullable();
            $table->decimal('preco_referencia', 12, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
        Schema::create('itens_pedido_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_compra_id')->constrained('pedidos_compra')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->string('descricao', 200);
            $table->string('unidade', 10)->default('UN');
            $table->decimal('quantidade', 12, 2)->default(1);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('itens_pedido_compra');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('pedidos_compra');
    }
};
