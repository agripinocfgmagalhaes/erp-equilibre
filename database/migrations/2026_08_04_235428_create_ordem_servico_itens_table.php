<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('ordem_servico_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            $table->foreignId('orcamento_item_id')->nullable()->constrained('orcamento_itens')->onDelete('set null');
            $table->foreignId('servico_id')->nullable()->constrained('servicos')->onDelete('set null');
            $table->string('descricao');
            $table->string('unidade', 10)->nullable();
            $table->decimal('quantidade_contratada', 12, 2)->default(0);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ordem_servico_itens'); }
};
