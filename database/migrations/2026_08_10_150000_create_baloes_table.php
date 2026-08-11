<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('baloes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_venda_id')->constrained('contratos_venda')->onDelete('cascade');
            $table->integer('ordem')->default(1);
            $table->string('descricao', 100)->default('Balão');
            $table->decimal('valor', 12, 2);
            $table->date('data_vencimento');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('baloes'); }
};
