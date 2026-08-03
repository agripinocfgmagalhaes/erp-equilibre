<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('prestadores', function (Blueprint $table) {
            if (! Schema::hasColumn('prestadores', 'especialidade')) {
                $table->string('especialidade')->nullable()->after('nome');
            }
        });
    }
    public function down(): void {
        Schema::table('prestadores', function (Blueprint $table) {
            if (Schema::hasColumn('prestadores', 'especialidade')) {
                $table->dropColumn('especialidade');
            }
        });
    }
};
