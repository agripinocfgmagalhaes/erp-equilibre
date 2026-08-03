<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('medicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            $table->integer('numero')->default(1);
            $table->date('data_medicao');
            $table->date('data_inicio_periodo');
            $table->date('data_fim_periodo');
            $table->decimal('valor_total', 15, 2);
            $table->decimal('percentual_acumulado', 5, 2)->default(0);
            $table->enum('status', ['rascunho', 'medida', 'aprovada', 'faturada', 'paga'])->default('rascunho');
            $table->date('data_aprovacao')->nullable();
            $table->foreignId('usuario_medicao_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observacoes')->nullable();
            $table->foreignId('conta_pagar_id')->nullable()->constrained('contas_pagar')->onDelete('set null');
            $table->timestamps();
            $table->unique(['ordem_servico_id', 'numero']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('medicoes');
    }
};
