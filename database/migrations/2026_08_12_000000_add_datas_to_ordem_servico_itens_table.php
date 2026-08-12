<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            if (! Schema::hasColumn('ordem_servico_itens', 'data_inicio_prevista')) {
                $table->date('data_inicio_prevista')->nullable()->after('valor_total');
                $table->date('data_fim_prevista')->nullable()->after('data_inicio_prevista');
                $table->date('data_conclusao_real')->nullable()->after('data_fim_prevista');
            }
        });

        // backfill: usa as datas da OS-mãe como default nos itens já existentes
        DB::table('ordem_servico_itens as osi')
            ->join('ordens_servico as os', 'os.id', '=', 'osi.ordem_servico_id')
            ->update([
                'osi.data_inicio_prevista' => DB::raw('os.data_inicio'),
                'osi.data_fim_prevista' => DB::raw('os.data_previsao_fim'),
                'osi.data_conclusao_real' => DB::raw('os.data_conclusao'),
            ]);
    }

    public function down(): void
    {
        Schema::table('ordem_servico_itens', function (Blueprint $table) {
            $table->dropColumn(['data_inicio_prevista', 'data_fim_prevista', 'data_conclusao_real']);
        });
    }
};
