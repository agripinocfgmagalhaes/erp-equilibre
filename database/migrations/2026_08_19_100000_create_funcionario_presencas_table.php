<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('funcionario_presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained()->cascadeOnDelete();
            $table->date('data');
            $table->boolean('presente')->default(true);
            $table->string('observacao')->nullable();
            $table->timestamps();
            $table->unique(['funcionario_id', 'data']);
        });
    }
    public function down(): void { Schema::dropIfExists('funcionario_presencas'); }
};
