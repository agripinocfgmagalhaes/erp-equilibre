<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contas_receber', function (Blueprint $table) {
            $table->string('inter_codigo_solicitacao')->nullable()->after('status');
            $table->string('inter_nosso_numero')->nullable();
            $table->string('inter_situacao')->nullable();
            $table->string('inter_linha_digitavel')->nullable();
            $table->text('inter_pix_copia_cola')->nullable();
            $table->timestamp('inter_emitido_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contas_receber', function (Blueprint $table) {
            $table->dropColumn(['inter_codigo_solicitacao','inter_nosso_numero','inter_situacao','inter_linha_digitavel','inter_pix_copia_cola','inter_emitido_em']);
        });
    }
};
