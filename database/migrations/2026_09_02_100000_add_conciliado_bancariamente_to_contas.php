<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contas_receber', function (Blueprint $table) {
            if (! Schema::hasColumn('contas_receber', 'conciliado_bancariamente')) {
                $table->boolean('conciliado_bancariamente')->default(false)->after('status');
            }
        });
        Schema::table('contas_pagar', function (Blueprint $table) {
            if (! Schema::hasColumn('contas_pagar', 'conciliado_bancariamente')) {
                $table->boolean('conciliado_bancariamente')->default(false)->after('status');
            }
        });
    }
    public function down(): void
    {
        Schema::table('contas_receber', function (Blueprint $table) { $table->dropColumn('conciliado_bancariamente'); });
        Schema::table('contas_pagar', function (Blueprint $table) { $table->dropColumn('conciliado_bancariamente'); });
    }
};
