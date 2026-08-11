<?php

namespace App\Console\Commands;

use App\Models\FasePadrao;
use App\Models\OrcamentoItem;
use App\Models\Projeto;
use Illuminate\Console\Command;

class ImportarOrcamentoCsv extends Command
{
    protected $signature = 'orcamento:importar {projeto_id} {caminho}';

    protected $description = 'Importa itens de orçamento (material/mdo/outros) a partir do CSV padrão de custos, direto no projeto';

    public function handle(): int
    {
        $projeto = Projeto::findOrFail($this->argument('projeto_id'));
        $caminho = $this->argument('caminho');

        if (! file_exists($caminho)) {
            $this->error("Arquivo não encontrado: {$caminho}");
            return self::FAILURE;
        }

        $linhas = array_map('str_getcsv', file($caminho));

        $headerIndex = null;
        foreach ($linhas as $i => $linha) {
            if (trim($linha[1] ?? '') === 'Descricao') {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->error('Cabeçalho (#,Descricao,Unid...) não localizado no CSV.');
            return self::FAILURE;
        }

        $faseAtual = null;
        $criados = 0;
        $faseNaoEncontrada = [];

        for ($i = $headerIndex + 1; $i < count($linhas); $i++) {
            $linha = $linhas[$i];
            $codigo = trim($linha[0] ?? '');
            $descricao = trim($linha[1] ?? '');
            $unidade = trim($linha[2] ?? '');

            if ($descricao === '') {
                continue;
            }

            $codigoNumPuro = str_replace(',', '', $codigo);
            $ehFase = $codigo !== '' && ctype_digit($codigoNumPuro) && $unidade === '';

            if ($ehFase) {
                $faseAtual = FasePadrao::where('numero', (int) $codigoNumPuro)->first();
                if (! $faseAtual) {
                    $faseNaoEncontrada[] = "{$codigoNumPuro} - {$descricao}";
                }
                continue;
            }

            if ($faseAtual === null) {
                continue;
            }

            $qtd = $this->numero($linha[3] ?? null);
            $materialUnit = $this->numero($linha[4] ?? null);
            $mdoUnit = $this->numero($linha[5] ?? null);
            $outrosUnit = $this->numero($linha[6] ?? null);

            OrcamentoItem::create([
                'projeto_id' => $projeto->id,
                'fase_padrao_id' => $faseAtual->id,
                'descricao' => $descricao,
                'unidade' => $unidade !== '' ? $unidade : null,
                'quantidade' => $qtd,
                'material_unitario' => $materialUnit,
                'mdo_unitario' => $mdoUnit,
                'outros_unitario' => $outrosUnit,
            ]);

            $criados++;
        }

        $this->info("Importação concluída: {$criados} itens criados.");

        if (! empty($faseNaoEncontrada)) {
            $this->warn('Fases do CSV sem correspondência em fases_padrao (itens ignorados):');
            foreach (array_unique($faseNaoEncontrada) as $f) {
                $this->line(" - {$f}");
            }
        }

        return self::SUCCESS;
    }

    private function numero(?string $valor): float
    {
        if ($valor === null) {
            return 0.0;
        }

        $valor = trim($valor);

        if ($valor === '' || $valor === '-') {
            return 0.0;
        }

        $valor = str_replace('%', '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return (float) $valor;
    }
}
