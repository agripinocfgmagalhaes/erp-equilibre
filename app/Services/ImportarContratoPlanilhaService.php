<?php
namespace App\Services;

use App\Models\Balao;
use App\Models\Cliente;
use App\Models\Corretor;
use App\Models\ContratoVenda;
use App\Models\Imobiliaria;
use App\Models\Projeto;
use App\Models\Unidade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ImportarContratoPlanilhaService
{
    // indice de cada coluna na linha colada (formato do Google Forms "Contrato de Venda Moderno")
    private const COL = [
        'empreendimento' => 1, 'unidade' => 2, 'corretor' => 3, 'cliente' => 4, 'cpf' => 5,
        'profissao' => 6, 'estado_civil' => 7, 'telefone' => 8, 'email' => 9, 'endereco' => 10,
        'conjuge_nome' => 11, 'conjuge_cpf' => 12, 'conjuge_profissao' => 13, 'conjuge_telefone' => 14, 'conjuge_email' => 15,
        'cep' => 18,
        'saldo_fsf' => 19, 'valor_imovel' => 20, 'financiamento' => 21, 'subsidio' => 22,
        'entrada' => 23, 'fgts' => 24, 'sinal' => 25, 'entrada_parcelar' => 26,
        'qtd_parcelas' => 27, 'juros' => 28, 'valor_parcela' => 29, 'data_parcela' => 30,
        'balao1' => 31, 'venc_balao1' => 32, 'balao2' => 33, 'venc_balao2' => 34, 'balao3' => 35, 'venc_balao3' => 36,
        'bonus' => 38, 'data_contrato' => 39, 'data_entrega' => 41, 'qualificacao' => 42,
    ];

    private function money(string $v): float
    {
        $v = trim($v);
        if ($v === '') return 0.0;
        return (float) str_replace(',', '.', str_replace('.', '', $v));
    }

    private function percent(string $v): float
    {
        return $this->money(str_replace('%', '', $v));
    }

    private function date(string $v): ?string
    {
        $v = trim($v);
        if ($v === '') return null;
        return Carbon::createFromFormat('d/m/Y', $v)->toDateString();
    }

    private function estadoCivil(string $v): ?string
    {
        $v = mb_strtolower(trim($v));
        $v = str_replace(['(a)', '(o)'], '', $v);
        $v = trim($v);
        $mapa = ['solteiro' => 'solteiro', 'casado' => 'casado', 'divorciado' => 'divorciado', 'viuvo' => 'viuvo', 'viúvo' => 'viuvo', 'uniao estavel' => 'uniao_estavel', 'união estável' => 'uniao_estavel'];
        return $mapa[$v] ?? null;
    }

    public function importar(string $linha): ContratoVenda
    {
        $campos = explode("\t", $linha);
        if (count($campos) < 43) {
            throw new InvalidArgumentException('Linha colada tem ' . count($campos) . ' colunas, esperado pelo menos 43. Confira se copiou a linha inteira (com os TABs) e não só o texto visível.');
        }

        $get = fn (string $chave) => trim($campos[self::COL[$chave]] ?? '');

        return DB::transaction(function () use ($get) {
            // Empreendimento / Unidade
            $nomeEmpreendimento = $get('empreendimento');
            $identificacaoUnidade = $get('unidade');

            $projeto = Projeto::where('nome', 'like', "%{$nomeEmpreendimento}%")->first();
            if (!$projeto) {
                throw new InvalidArgumentException("Empreendimento \"{$nomeEmpreendimento}\" não encontrado. Cadastre o Empreendimento antes de importar, ou confira o nome.");
            }

            $unidade = Unidade::where('projeto_id', $projeto->id)
                ->where('identificacao', 'like', "%{$identificacaoUnidade}%")
                ->first();
            if (!$unidade) {
                throw new InvalidArgumentException("Unidade \"{$identificacaoUnidade}\" não encontrada no empreendimento \"{$projeto->nome}\". Cadastre a Unidade antes de importar, ou confira o identificador.");
            }

            // Cliente (por CPF)
            $cpf = preg_replace('/\D/', '', $get('cpf'));
            $cliente = Cliente::where('cpf', $cpf)->first();
            $dadosCliente = [
                'nome' => $get('cliente'),
                'cpf' => $cpf,
                'email' => $get('email') ?: null,
                'telefone' => $get('telefone') ?: null,
                'profissao' => $get('profissao') ?: null,
                'logradouro' => $get('endereco') ?: null,
                'cep' => preg_replace('/\D/', '', $get('cep')) ?: null,
                'conjuge_nome' => $get('conjuge_nome') ?: null,
                'conjuge_cpf' => preg_replace('/\D/', '', $get('conjuge_cpf')) ?: null,
                'conjuge_profissao' => $get('conjuge_profissao') ?: null,
                'conjuge_telefone' => $get('conjuge_telefone') ?: null,
                'conjuge_email' => $get('conjuge_email') ?: null,
            ];
            $estadoCivil = $this->estadoCivil($get('estado_civil'));
            if ($estadoCivil) $dadosCliente['estado_civil'] = $estadoCivil;

            if ($cliente) {
                $cliente->update(array_filter($dadosCliente, fn ($v) => $v !== null && $v !== ''));
            } else {
                $cliente = Cliente::create($dadosCliente);
            }

            // Corretor / Imobiliaria (campo vem como "Nome do Corretor/ Nome da Imobiliaria")
            $corretor = null;
            $dadosCorretor = $get('corretor');
            if ($dadosCorretor !== '') {
                $partes = array_map('trim', explode('/', $dadosCorretor, 2));
                $nomeCorretor = $partes[0];
                $nomeImobiliaria = $partes[1] ?? null;

                $imobiliaria = null;
                if ($nomeImobiliaria) {
                    $imobiliaria = Imobiliaria::firstOrCreate(['nome' => $nomeImobiliaria]);
                }

                $corretor = Corretor::where('nome', $nomeCorretor)->first();
                if (!$corretor) {
                    $corretor = Corretor::create([
                        'nome' => $nomeCorretor,
                        'imobiliaria_id' => $imobiliaria?->id,
                        'ativo' => true,
                    ]);
                }
            }

            // Contrato de Venda
            $valorVenda = $this->money($get('valor_imovel'));
            $valorSinal = $this->money($get('sinal'));
            $valorFgts = $this->money($get('fgts'));
            $valorSubsidio = $this->money($get('subsidio'));
            $valorFinanciamento = $this->money($get('financiamento'));
            $valorParcelamento = $this->money($get('entrada_parcelar'));
            $qtdParcelas = (int) $this->money($get('qtd_parcelas'));
            $taxaJuros = $this->percent($get('juros')) ?: 1.2;
            $valorParcela = $this->money($get('valor_parcela'));
            $bonus = $this->money($get('bonus'));

            $observacoes = trim(implode("\n", array_filter([
                $bonus > 0 ? 'Bônus: R$ ' . number_format($bonus, 2, ',', '.') : null,
                $get('qualificacao') ? 'Qualificação: ' . $get('qualificacao') : null,
            ])));

            $contrato = ContratoVenda::create([
                'unidade_id' => $unidade->id,
                'cliente_id' => $cliente->id,
                'corretor_id' => $corretor?->id,
                'status' => 'ativo',
                'valor_venda' => $valorVenda,
                'valor_sinal' => $valorSinal,
                'valor_fgts' => $valorFgts,
                'valor_subsidio' => $valorSubsidio,
                'valor_financiamento' => $valorFinanciamento,
                'valor_parcelamento' => $valorParcelamento,
                'qtd_parcelas' => $qtdParcelas,
                'taxa_juros' => $taxaJuros,
                'valor_parcela' => $valorParcela,
                'data_contrato' => $this->date($get('data_contrato')) ?? now()->toDateString(),
                'data_entrega_prevista' => $this->date($get('data_entrega')),
                'observacoes' => $observacoes ?: null,
            ]);

            // Balões (até 3): valor + vencimento; ignora os vazios/zerados
            foreach ([1, 2, 3] as $n) {
                $valorBalao = $this->money($get("balao{$n}"));
                if ($valorBalao <= 0) continue;
                $vencBalao = $this->date($get("venc_balao{$n}"));
                if (!$vencBalao) {
                    throw new InvalidArgumentException("Balão {$n} tem valor mas está sem data de vencimento na planilha.");
                }
                Balao::create([
                    'contrato_venda_id' => $contrato->id,
                    'ordem' => $n,
                    'descricao' => 'Balão',
                    'valor' => $valorBalao,
                    'data_vencimento' => $vencBalao,
                ]);
            }

            return $contrato;
        });
    }
}
