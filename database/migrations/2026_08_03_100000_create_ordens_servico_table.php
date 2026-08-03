<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('data');
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->foreignId('prestador_id')->nullable()->constrained('prestadores')->onDelete('set null');
            $table->foreignId('fase_obra_id')->nullable()->constrained('fases_obra')->onDelete('set null');
            $table->text('descricao')->nullable();
            $table->decimal('valor_total', 15, 2);
            $table->date('data_inicio')->nullable();
            $table->date('data_previsao_fim')->nullable();
            $table->date('data_conclusao')->nullable();
            $table->enum('status', ['planejado', 'em_execucao', 'concluido', 'suspenso'])->default('planejado');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ordens_servico');
    }
};
