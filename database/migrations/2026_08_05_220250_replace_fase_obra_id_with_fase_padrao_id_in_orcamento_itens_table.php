<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            if (Schema::hasColumn('orcamento_itens', 'fase_obra_id')) $table->dropConstrainedForeignId('fase_obra_id');
            if (! Schema::hasColumn('orcamento_itens', 'fase_padrao_id')) {
                $table->foreignId('fase_padrao_id')->nullable()->after('projeto_id')->constrained('fases_padrao')->onDelete('set null');
            }
        });
    }
    public function down(): void {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            if (Schema::hasColumn('orcamento_itens', 'fase_padrao_id')) $table->dropConstrainedForeignId('fase_padrao_id');
        });
    }
};
