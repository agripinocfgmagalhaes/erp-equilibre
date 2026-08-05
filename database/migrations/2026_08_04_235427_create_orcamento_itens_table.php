<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orcamento_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->onDelete('cascade');
            $table->foreignId('fase_obra_id')->nullable()->constrained('fases_obra')->onDelete('set null');
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->onDelete('set null');
            $table->string('descricao');
            $table->string('unidade', 10)->nullable();
            $table->decimal('quantidade', 12, 2)->default(0);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orcamento_itens'); }
};
