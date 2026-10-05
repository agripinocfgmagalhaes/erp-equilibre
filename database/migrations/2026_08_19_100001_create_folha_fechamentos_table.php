<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('folha_fechamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained()->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->unsignedSmallInteger('dias_trabalhados')->default(0);
            $table->decimal('valor_diaria', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('status')->default('fechado');
            $table->foreignId('conta_pagar_id')->nullable()->constrained('contas_pagar')->nullOnDelete();
            $table->timestamps();
            $table->unique(['funcionario_id', 'periodo_inicio', 'periodo_fim'], 'folha_fecham_func_periodo_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('folha_fechamentos'); }
};
