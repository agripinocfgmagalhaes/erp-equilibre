<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('corretores', 'whatsapp')) {
            Schema::table('corretores', function (Blueprint $table) {
                $table->dropColumn('whatsapp');
            });
        }
    }
    public function down(): void {
        Schema::table('corretores', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable();
        });
    }
};
