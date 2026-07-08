<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 200);
            $table->string('contato_tipo', 20)->nullable();
            $table->unsignedBigInteger('contato_id')->nullable();
            $table->foreignId('plano_conta_id')->nullable()->constrained('plano_contas')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->nullOnDelete();
            $table->foreignId('fase_obra_id')->nullable()->constrained('fases_obra')->nullOnDelete();
            $table->foreignId('pedido_compra_id')->nullable()->constrained('pedidos_compra')->nullOnDelete();
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['aberto','pago','vencido','cancelado'])->default('aberto');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('contas_pagar'); }
};
