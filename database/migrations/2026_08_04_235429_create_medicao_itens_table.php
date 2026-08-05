<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('medicao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicao_id')->constrained('medicoes')->onDelete('cascade');
            $table->foreignId('ordem_servico_item_id')->constrained('ordem_servico_itens')->onDelete('cascade');
            $table->decimal('quantidade_periodo', 12, 2)->default(0);
            $table->decimal('quantidade_acumulada', 12, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medicao_itens'); }
};
