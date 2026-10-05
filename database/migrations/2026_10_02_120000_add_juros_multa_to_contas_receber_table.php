<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (! Schema::hasColumn('contas_receber', 'juros_multa')) {
            Schema::table('contas_receber', function (Blueprint $t) {
                $t->decimal('juros_multa', 15, 2)->virtualAs('GREATEST(valor_recebido - valor, 0)')->after('valor_recebido');
            });
        }
    }
    public function down(): void {
        Schema::table('contas_receber', fn (Blueprint $t) => $t->dropColumn('juros_multa'));
    }
};
