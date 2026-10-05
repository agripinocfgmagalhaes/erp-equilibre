<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Models\PlanoConta;
use App\Models\Projeto;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportarContasReceberCsv extends Command
{
    protected $signature = 'contas-receber:importar-csv {caminho}';
    protected $description = 'Importa contas a receber em aberto a partir de um CSV exportado da planilha';

    public function handle(): int
    {
        $caminho = $this->argument('caminho');

        if (! file_exists($caminho)) {
            $this->error("Arquivo não encontrado: {$caminho}");
            return self::FAILURE;
        }

        $handle = fopen($caminho, 'r');
        fgetcsv($handle, 0, ','); // header

        $clientesCache = [];
        $projetosCache = [];
        $planosCache = [];
        $contasCache = [];

        $criadas = 0;
        $atualizadas = 0;
        $linha = 1;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $linha++;

            if (count($row) < 8 || trim((string) $row[7]) === '') {
                continue;
            }

            [$data, $nota, $projetoNome, $planoNome, $clienteNome, $descricao] = array_pad($row, 6, '');
            $entrada = $row[7];
            $contaNome = $row[10] ?? '';

            $dataVencimento = \DateTime::createFromFormat('d/m/Y', trim($data));
            if (! $dataVencimento) {
                $this->warn("Linha {$linha}: data inválida '{$data}', pulando.");
                continue;
            }

            $valor = (float) str_replace(['.', ','], ['', '.'], trim($entrada));
            $clienteNome = trim(preg_replace('/\s+/', ' ', (string) $clienteNome));
            $projetoNome = trim((string) $projetoNome);
            $planoNome = trim((string) $planoNome) ?: 'Vendas';
            $contaNome = trim((string) $contaNome);

            if ($clienteNome === '') {
                $this->warn("Linha {$linha}: sem cliente, pulando.");
                continue;
            }

            $chaveCliente = Str::upper($clienteNome);
            if (! isset($clientesCache[$chaveCliente])) {
                $clientesCache[$chaveCliente] = Cliente::whereRaw('UPPER(nome) = ?', [$chaveCliente])->first()
                    ?? Cliente::create(['nome' => $clienteNome, 'ativo' => true]);
            }
            $cliente = $clientesCache[$chaveCliente];

            $projeto = null;
            if ($projetoNome !== '') {
                $chaveProjeto = Str::upper($projetoNome);
                if (! isset($projetosCache[$chaveProjeto])) {
                    $projetosCache[$chaveProjeto] = Projeto::whereRaw('UPPER(nome) = ?', [$chaveProjeto])->first()
                        ?? Projeto::create(['nome' => $projetoNome, 'status' => 'em_andamento']);
                }
                $projeto = $projetosCache[$chaveProjeto];
            }

            $chavePlano = Str::upper($planoNome);
            if (! isset($planosCache[$chavePlano])) {
                $planosCache[$chavePlano] = PlanoConta::whereRaw('UPPER(nome) = ?', [$chavePlano])->first()
                    ?? PlanoConta::create([
                        'codigo' => 'REC-' . Str::upper(Str::slug($planoNome, '')),
                        'nome' => $planoNome,
                        'tipo' => 'receita',
                        'ativo' => true,
                    ]);
            }
            $plano = $planosCache[$chavePlano];

            $contaBancaria = null;
            if ($contaNome !== '') {
                $chaveConta = Str::upper($contaNome);
                if (! isset($contasCache[$chaveConta])) {
                    $contasCache[$chaveConta] = ContaBancaria::whereRaw('UPPER(nome) = ?', [$chaveConta])->first()
                        ?? ContaBancaria::create([
                            'nome' => $contaNome,
                            'tipo' => str_contains(Str::lower($contaNome), 'caixa') ? 'caixa' : 'corrente',
                            'ativo' => true,
                        ]);
                }
                $contaBancaria = $contasCache[$chaveConta];
            }

            $numeroDocumento = Str::limit(trim((string) $nota), 50, '');
            $descricaoFinal = Str::limit(trim(($projetoNome ? $projetoNome . ' - ' : '') . trim((string) $descricao)) ?: 'Parcela venda', 200, '');

            $registro = ContaReceber::updateOrCreate(
                [
                    'cliente_id' => $cliente->id,
                    'numero_documento' => $numeroDocumento ?: null,
                    'data_vencimento' => $dataVencimento->format('Y-m-d'),
                    'valor' => $valor,
                ],
                [
                    'descricao' => $descricaoFinal,
                    'plano_conta_id' => $plano->id,
                    'conta_bancaria_id' => $contaBancaria?->id,
                    'projeto_id' => $projeto?->id,
                    'status' => 'aberto',
                ]
            );

            $registro->wasRecentlyCreated ? $criadas++ : $atualizadas++;
        }

        fclose($handle);

        $this->info("Importação concluída: {$criadas} criadas, {$atualizadas} atualizadas/já existentes.");

        return self::SUCCESS;
    }
}
