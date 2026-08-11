<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequencias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->unsignedSmallInteger('ano');
            $table->unsignedBigInteger('valor')->default(0);
            $table->unique(['tipo', 'ano']);
            $table->timestamps();
        });

        // Semear contadores com a quantidade atual para dar continuidade à numeração.
        $ano = now()->year;
        $origens = [
            'RC' => 'requisicoes_compra',
            'PC' => 'pedidos_compra',
            'CV' => 'contratos_venda',
        ];

        foreach ($origens as $tipo => $tabela) {
            if (! Schema::hasTable($tabela)) {
                continue;
            }
            $quantidade = DB::table($tabela)->count();
            DB::table('sequencias')->insert([
                'tipo' => $tipo,
                'ano' => $ano,
                'valor' => $quantidade,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sequencias');
    }
};
