<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('prestadores', function (Blueprint $table) {
            $table->string('chave_pix')->nullable()->after('telefone');
            $table->string('tipo_chave_pix', 20)->nullable()->after('chave_pix');
        });
    }
    public function down(): void {
        Schema::table('prestadores', function (Blueprint $table) {
            $table->dropColumn(['chave_pix','tipo_chave_pix']);
        });
    }
};
