<?php

namespace App\Console\Commands;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Console\Command;

class AtualizarStatusVencidos extends Command
{
    protected $signature = 'financeiro:atualizar-vencidos';
    protected $description = 'Marca como vencido os títulos em aberto com vencimento passado (e reabre os que deixaram de estar vencidos)';

    public function handle(): int
    {
        foreach ([ContaReceber::class, ContaPagar::class] as $modelo) {
            $vencidos = $modelo::where('status', 'aberto')->whereDate('data_vencimento', '<', today())->update(['status' => 'vencido']);
            $reabertos = $modelo::where('status', 'vencido')->whereDate('data_vencimento', '>=', today())->update(['status' => 'aberto']);
            $this->info(class_basename($modelo) . ": {$vencidos} vencido(s), {$reabertos} reaberto(s)");
        }
        return self::SUCCESS;
    }
}
