<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'conjuge_profissao')) {
                $table->string('conjuge_profissao', 100)->nullable()->after('conjuge_renda');
            }
            if (! Schema::hasColumn('clientes', 'conjuge_email')) {
                $table->string('conjuge_email', 100)->nullable()->after('conjuge_profissao');
            }
            if (! Schema::hasColumn('clientes', 'conjuge_telefone')) {
                $table->string('conjuge_telefone', 20)->nullable()->after('conjuge_email');
            }
        });
    }
    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['conjuge_profissao', 'conjuge_email', 'conjuge_telefone']);
        });
    }
};
