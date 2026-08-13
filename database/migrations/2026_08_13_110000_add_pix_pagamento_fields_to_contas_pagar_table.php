<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->string('chave_pix_destino')->nullable()->after('status');
            $table->string('tipo_chave_pix_destino', 20)->nullable();
            $table->string('inter_pix_e2e_id')->nullable();
            $table->string('inter_pix_status')->nullable();
            $table->timestamp('inter_pix_enviado_em')->nullable();
        });
    }
    public function down(): void {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropColumn(['chave_pix_destino','tipo_chave_pix_destino','inter_pix_e2e_id','inter_pix_status','inter_pix_enviado_em']);
        });
    }
};
