<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('configuracoes_inter', function (Blueprint $table) {
            $table->foreignId('conta_bancaria_id')->nullable()->after('ambiente')->constrained('contas_bancarias')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('configuracoes_inter', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conta_bancaria_id');
        });
    }
};
