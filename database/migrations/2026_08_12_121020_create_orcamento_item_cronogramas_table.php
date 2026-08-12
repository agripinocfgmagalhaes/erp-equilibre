<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orcamento_item_cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_item_id')->constrained('orcamento_itens')->cascadeOnDelete();
            $table->date('mes');
            $table->decimal('percentual', 5, 2);
            $table->timestamps();
            $table->unique(['orcamento_item_id', 'mes']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('orcamento_item_cronogramas');
    }
};
