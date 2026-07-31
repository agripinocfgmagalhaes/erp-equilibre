<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_inter', function (Blueprint $table) {
            $table->id();
            $table->enum('ambiente', ['sandbox', 'producao'])->default('sandbox');
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('conta_corrente')->nullable();
            $table->string('cedente_cnpj')->nullable();
            $table->string('cert_path')->nullable();
            $table->string('key_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_inter');
    }
};
