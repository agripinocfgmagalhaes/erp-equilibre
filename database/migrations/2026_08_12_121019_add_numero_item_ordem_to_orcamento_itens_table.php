<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            if (! Schema::hasColumn('orcamento_itens', 'numero_item')) {
                $table->string('numero_item', 20)->nullable()->after('fase_padrao_id');
            }
            if (! Schema::hasColumn('orcamento_itens', 'ordem')) {
                $table->unsignedInteger('ordem')->nullable()->after('numero_item');
            }
        });
        Schema::table('orcamento_itens', function (Blueprint $table) {
            $table->unique(['projeto_id', 'numero_item'], 'orcamento_itens_projeto_numero_unique');
        });
    }
    public function down(): void {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            $table->dropUnique('orcamento_itens_projeto_numero_unique');
            $table->dropColumn(['numero_item', 'ordem']);
        });
    }
};
