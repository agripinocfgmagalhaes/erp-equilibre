<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imobiliarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('creci', 20)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->timestamps();
        });

        Schema::table('corretores', function (Blueprint $table) {
            $table->foreignId('imobiliaria_id')->nullable()->after('id')->constrained('imobiliarias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('corretores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('imobiliaria_id');
        });
        Schema::dropIfExists('imobiliarias');
    }
};
