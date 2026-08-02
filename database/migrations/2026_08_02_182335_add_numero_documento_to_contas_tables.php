<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->string('numero_documento', 50)->nullable()->after('descricao');
        });
        Schema::table('contas_receber', function (Blueprint $table) {
            $table->string('numero_documento', 50)->nullable()->after('descricao');
        });
    }
    public function down(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropColumn('numero_documento');
        });
        Schema::table('contas_receber', function (Blueprint $table) {
            $table->dropColumn('numero_documento');
        });
    }
};
