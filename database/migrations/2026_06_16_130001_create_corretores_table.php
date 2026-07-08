<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('corretores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cpf_cnpj', 18)->nullable()->unique();
            $table->string('creci', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('corretores'); }
};
