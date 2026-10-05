<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('contratos_venda', function (Blueprint $table) {
            $table->date('data_primeira_parcela')->nullable()->after('data_entrega_prevista');
        });
    }
    public function down(): void
    {
        Schema::table('contratos_venda', function (Blueprint $table) {
            $table->dropColumn('data_primeira_parcela');
        });
    }
};
