<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('unidades', function (Blueprint $table) {
            if (Schema::hasColumn('unidades', 'bloco')) {
                $table->dropColumn(['bloco']);
            }
        });
    }

    public function down(): void {
        Schema::table('unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('unidades', 'bloco')) {
                $table->string('bloco', 20)->nullable()->after('identificacao');
            }
        });
    }
};
