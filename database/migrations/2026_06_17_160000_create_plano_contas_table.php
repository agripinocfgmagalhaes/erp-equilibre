<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plano_contas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nome', 100);
            $table->enum('tipo', ['despesa','receita'])->default('despesa');
            $table->foreignId('plano_conta_pai_id')->nullable()->constrained('plano_contas')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
        Schema::create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('banco', 100)->nullable();
            $table->string('agencia', 20)->nullable();
            $table->string('conta', 30)->nullable();
            $table->enum('tipo', ['corrente','poupanca','caixa'])->default('corrente');
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('contas_bancarias');
        Schema::dropIfExists('plano_contas');
    }
};
