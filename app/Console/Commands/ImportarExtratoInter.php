<?php
namespace App\Console\Commands;

use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\LancamentoBancario;
use App\Services\InterExtratoService;
use Illuminate\Console\Command;

class ImportarExtratoInter extends Command
{
    protected $signature = 'financeiro:importar-extrato {--dias=7}';
    protected $description = 'Importa o extrato do Inter para lancamentos_bancarios e tenta conciliar automaticamente Pix enviados';

    public function handle(InterExtratoService $service): int
    {
        $dias = (int) $this->option('dias');
        $dataInicio = now()->subDays($dias)->format('Y-m-d');
        $dataFim = now()->format('Y-m-d');

        $config = \App\Models\ConfiguracaoInter::atual();
        $contaBancaria = $config->conta_bancaria_id ? ContaBancaria::find($config->conta_bancaria_id) : null;
        if (!$contaBancaria) {
            $this->error('Nenhuma ContaBancaria vinculada a integracao Inter. Configure em Configuracoes > Integracao Inter.');
            return self::FAILURE;
        }

        $resposta = $service->buscarExtrato($dataInicio, $dataFim);
        $transacoes = $resposta['transacoes'] ?? $resposta;

        $novos = 0; $conciliadosAuto = 0; $duplicados = 0;

        foreach ($transacoes as $t) {
            $hash = md5(($t['dataEntrada'] ?? '') . '|' . ($t['tipoTransacao'] ?? '') . '|' . ($t['tipoOperacao'] ?? '') . '|' . ($t['valor'] ?? '') . '|' . ($t['descricao'] ?? ''));

            if (LancamentoBancario::where('inter_transacao_id', $hash)->exists()) {
                $duplicados++;
                continue;
            }

            $lancamento = LancamentoBancario::create([
                'conta_bancaria_id' => $contaBancaria->id,
                'tipo' => ($t['tipoOperacao'] ?? '') === 'C' ? 'entrada' : 'saida',
                'descricao' => $t['descricao'] ?? ($t['titulo'] ?? 'Lancamento Inter'),
                'valor' => (float) ($t['valor'] ?? 0),
                'data' => $t['dataEntrada'] ?? now()->toDateString(),
                'origem' => 'extrato_inter',
                'inter_transacao_id' => $hash,
                'conciliado' => false,
            ]);
            $novos++;

            if (($t['tipoOperacao'] ?? '') === 'D' && ($t['tipoTransacao'] ?? '') === 'PIX') {
                $candidatos = ContaPagar::whereNotNull('inter_pix_e2e_id')
                    ->whereDate('inter_pix_enviado_em', $lancamento->data)
                    ->where('valor', $lancamento->valor)
                    ->get();

                if ($candidatos->count() === 1) {
                    $conta = $candidatos->first();
                    $lancamento->update(['conciliado' => true, 'conciliado_em' => now(), 'origem_id' => $conta->id]);
                    if ($conta->status !== 'pago') {
                        $conta->darBaixa($lancamento->valor, $lancamento->data);
                    }
                    $conciliadosAuto++;
                }
            }
        }

        $this->info("Importados: {$novos} | Conciliados automaticamente: {$conciliadosAuto} | Ja existiam: {$duplicados}");
        return self::SUCCESS;
    }
}
