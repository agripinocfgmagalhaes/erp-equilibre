<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->text('descricao')->nullable();
            $table->enum('status', ['planejamento','em_andamento','concluido','cancelado'])->default('planejamento');
            $table->date('data_inicio')->nullable();
            $table->date('data_previsao_fim')->nullable();
            $table->timestamps();
        });
        Schema::create('fases_obra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->integer('ordem')->default(0);
            $table->decimal('percentual', 5, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained('projetos')->cascadeOnDelete();
            $table->string('identificacao', 20);
            $table->string('tipo', 50)->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('valor_tabela', 15, 2)->default(0);
            $table->enum('status', ['disponivel','reservado','vendido','distratado'])->default('disponivel');
            $table->timestamps();
        });
        Schema::create('fases_padrao', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('macro_categoria', 100)->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('unidades');
        Schema::dropIfExists('fases_obra');
        Schema::dropIfExists('fases_padrao');
        Schema::dropIfExists('projetos');
    }
};
