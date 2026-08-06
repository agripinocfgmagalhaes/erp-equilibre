<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (! Schema::hasColumn('contas_pagar', 'fase_padrao_id')) {
                $table->foreignId('fase_padrao_id')->nullable()->after('fase_obra_id')->constrained('fases_padrao')->onDelete('set null');
            }
        });
    }
    public function down(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (Schema::hasColumn('contas_pagar', 'fase_padrao_id')) $table->dropConstrainedForeignId('fase_padrao_id');
        });
    }
};
