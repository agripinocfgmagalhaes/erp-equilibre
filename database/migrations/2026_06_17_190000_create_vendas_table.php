<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('contratos_venda', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('corretor_id')->nullable()->constrained('corretores')->nullOnDelete();
            $table->enum('status', ['ativo','distratado','cancelado'])->default('ativo');
            $table->decimal('valor_venda', 15, 2);
            $table->decimal('valor_entrada', 15, 2)->default(0);
            $table->decimal('valor_sinal', 15, 2)->default(0);
            $table->decimal('valor_parcelamento', 15, 2)->default(0);
            $table->decimal('valor_fgts', 15, 2)->default(0);
            $table->decimal('valor_financiamento', 15, 2)->default(0);
            $table->decimal('valor_subsidio', 15, 2)->default(0);
            $table->integer('qtd_parcelas')->default(0);
            $table->decimal('taxa_juros', 5, 3)->default(1.200);
            $table->decimal('valor_parcela', 15, 2)->default(0);
            $table->decimal('percentual_comissao', 5, 2)->default(0);
            $table->decimal('valor_comissao', 15, 2)->default(0);
            $table->date('data_contrato');
            $table->date('data_entrega_prevista')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 200);
            $table->foreignId('contrato_venda_id')->nullable()->constrained('contratos_venda')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('plano_conta_id')->nullable()->constrained('plano_contas')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();
            $table->foreignId('projeto_id')->nullable()->constrained('projetos')->nullOnDelete();
            $table->decimal('valor', 15, 2);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->enum('status', ['aberto','recebido','vencido','cancelado'])->default('aberto');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contas_receber');
        Schema::dropIfExists('contratos_venda');
    }
};
