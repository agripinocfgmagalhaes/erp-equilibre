<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('lancamentos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_bancaria_id')->constrained('contas_bancarias')->cascadeOnDelete();
            $table->enum('tipo', ['entrada','saida']);
            $table->string('descricao', 200);
            $table->decimal('valor', 15, 2);
            $table->date('data');
            $table->enum('origem', ['manual','conta_pagar','conta_receber'])->default('manual');
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lancamentos_bancarios'); }
};
