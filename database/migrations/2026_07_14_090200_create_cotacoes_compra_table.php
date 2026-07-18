<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('cotacoes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicao_compra_id')->constrained('requisicoes_compra')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->cascadeOnDelete();
            $table->date('data_cotacao');
            $table->unsignedInteger('prazo_entrega_dias')->nullable();
            $table->string('condicao_pagamento', 100)->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->string('arquivo_path')->nullable();
            $table->boolean('vencedora')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        Schema::create('itens_cotacao_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotacao_compra_id')->constrained('cotacoes_compra')->cascadeOnDelete();
            $table->foreignId('item_requisicao_compra_id')->constrained('itens_requisicao_compra')->cascadeOnDelete();
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('itens_cotacao_compra');
        Schema::dropIfExists('cotacoes_compra');
    }
};
