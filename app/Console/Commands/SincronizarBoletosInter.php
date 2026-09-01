<?php

namespace App\Console\Commands;

use App\Models\ContaReceber;
use App\Services\InterBoletoService;
use Illuminate\Console\Command;

class SincronizarBoletosInter extends Command
{
    protected $signature = 'boletos:sincronizar-inter {--de=2026-09-01} {--ate=} {--situacao=A_RECEBER} {--status=aberto}';
    protected $description = 'Casa boletos existentes no Inter (emitidos fora do sistema) com Contas a Receber, por CPF + valor + vencimento';

    public function handle(InterBoletoService $service): int
    {
        $de = $this->option('de');
        $ate = $this->option('ate') ?: now()->addMonths(2)->format('Y-m-d');
        $situacaoInter = $this->option('situacao');
        $statusCr = array_map('trim', explode(',', $this->option('status')));

        $this->info("Buscando cobrancas '{$situacaoInter}' no Inter de {$de} ate {$ate}...");
        $cobrancas = $service->listar($de, $ate, $situacaoInter);
        $this->info(count($cobrancas) . ' cobrancas encontradas no Inter.');

        $vinculados = 0; $jaVinculados = 0; $semMatch = 0; $dadosIncompletos = 0;
        $listaSemMatch = [];

        foreach ($cobrancas as $item) {
            $codigoSolicitacao = $item['cobranca']['codigoSolicitacao'] ?? null;
            $cpfPagador = preg_replace('/\D/', '', $item['cobranca']['pagador']['cpfCnpj'] ?? '');
            $nomePagador = $item['cobranca']['pagador']['nome'] ?? '-';
            $valor = (float) ($item['cobranca']['valorNominal'] ?? 0);
            $vencimento = $item['cobranca']['dataVencimento'] ?? null;
            $situacao = $item['cobranca']['situacao'] ?? '-';

            if (!$codigoSolicitacao || !$cpfPagador || !$vencimento) {
                $dadosIncompletos++;
                $this->warn("DADOS INCOMPLETOS: codigo={$codigoSolicitacao} cpf={$cpfPagador} venc={$vencimento} nome={$nomePagador}");
                continue;
            }

            if (ContaReceber::where('inter_codigo_solicitacao', $codigoSolicitacao)->exists()) {
                $jaVinculados++;
                continue;
            }

            $conta = ContaReceber::whereNull('inter_codigo_solicitacao')
                ->whereIn('status', $statusCr)
                ->where('data_vencimento', '>=', $de)
                ->where('valor', $valor)
                ->whereHas('cliente', function ($q) use ($cpfPagador) {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),'/',''),' ','') = ?", [$cpfPagador]);
                })
                ->whereBetween('data_vencimento', [
                    \Carbon\Carbon::parse($vencimento)->subDays(5),
                    \Carbon\Carbon::parse($vencimento)->addDays(5),
                ])
                ->first();

            if (!$conta) {
                $semMatch++;
                $linha = "SEM MATCH: {$nomePagador} | CPF {$cpfPagador} | R$ {$valor} | venc {$vencimento} | situacao {$situacao} | codigo {$codigoSolicitacao}";
                $this->warn($linha);
                $listaSemMatch[] = $linha;
                continue;
            }

            $conta->update([
                'inter_codigo_solicitacao' => $codigoSolicitacao,
                'inter_situacao' => $situacao,
                'inter_emitido_em' => $conta->inter_emitido_em ?? now(),
            ]);

            try {
                $service->consultar($conta);
                $this->info("VINCULADO: conta #{$conta->id} ({$conta->descricao}) -> {$codigoSolicitacao}");
                $vinculados++;
            } catch (\Exception $e) {
                $this->error("Vinculado mas falhou ao buscar detalhes da conta #{$conta->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Vinculados agora: {$vinculados}");
        $this->info("Ja vinculados antes: {$jaVinculados}");
        $this->warn("Sem match com Contas a Receber: {$semMatch}");
        $this->warn("Dados incompletos na resposta do Inter: {$dadosIncompletos}");

        if (!empty($listaSemMatch)) {
            $path = storage_path('app/boletos_sem_match_' . now()->format('Y-m-d_His') . '.txt');
            file_put_contents($path, implode(PHP_EOL, $listaSemMatch));
            $this->newLine();
            $this->info("Lista completa de 'sem match' salva em: {$path}");
        }

        return self::SUCCESS;
    }
}
