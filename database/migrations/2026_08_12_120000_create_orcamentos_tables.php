<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orcamentos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('projeto_id')->constrained('projetos')->cascadeOnDelete();
            $t->string('nome', 150);
            $t->date('data_base')->nullable();
            $t->decimal('area_construida', 10, 2)->nullable();
            $t->integer('numero_unidades')->nullable();
            $t->string('status', 20)->default('rascunho');
            $t->text('observacoes')->nullable();
            $t->timestamps();
        });
        Schema::create('orcamento_itens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('orcamento_itens')->cascadeOnDelete();
            $t->string('codigo', 20)->nullable();
            $t->string('descricao', 255);
            $t->string('tipo', 10)->default('item');
            $t->string('unidade', 10)->nullable();
            $t->decimal('quantidade', 12, 2)->nullable();
            $t->string('classificacao', 10)->nullable();
            $t->decimal('custo_unitario', 14, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('orcamento_cronograma', function (Blueprint $t) {
            $t->id();
            $t->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
            $t->string('codigo_item', 20);
            $t->date('competencia');
            $t->decimal('percentual', 8, 2)->default(0);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('orcamento_cronograma');
        Schema::dropIfExists('orcamento_itens');
        Schema::dropIfExists('orcamentos');
    }
};
