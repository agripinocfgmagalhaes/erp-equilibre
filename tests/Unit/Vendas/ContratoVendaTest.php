<?php

namespace Tests\Unit\Vendas;

use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\ContratoVenda;
use App\Models\Projeto;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContratoVendaTest extends TestCase
{
    use RefreshDatabase;

    private function criarCliente(): Cliente
    {
        return Cliente::create(['nome' => 'João da Silva']);
    }

    private function criarUnidade(array $attrs = []): Unidade
    {
        $projeto = Projeto::create(['nome' => 'Residencial Equilíbre']);

        return Unidade::create(array_merge([
            'projeto_id' => $projeto->id,
            'identificacao' => '105 A',
            'status' => 'disponivel',
        ], $attrs));
    }

    public function test_numero_eh_gerado_sequencialmente(): void
    {
        $ano = now()->year;
        $contrato = $this->criarContrato();

        $this->assertMatchesRegularExpression("/^CV-{$ano}-\d{4}$/", $contrato->numero);
    }

    public function test_criacao_calcula_comissao_e_vende_unidade(): void
    {
        $unidade = $this->criarUnidade();
        $contrato = ContratoVenda::create([
            'numero' => ContratoVenda::gerarNumero(),
            'unidade_id' => $unidade->id,
            'cliente_id' => $this->criarCliente()->id,
            'status' => 'ativo',
            'valor_venda' => 200000,
            'data_contrato' => now()->toDateString(),
        ]);

        $this->assertSame('4.50', $contrato->percentual_comissao);
        $this->assertSame('9000.00', $contrato->valor_comissao);
        $this->assertSame('vendido', $unidade->fresh()->status);
    }

    public function test_distratado_devolve_unidade_para_disponivel(): void
    {
        $unidade = $this->criarUnidade();
        $contrato = $this->criarContrato(['unidade_id' => $unidade->id]);
        $this->assertSame('vendido', $unidade->fresh()->status);

        $contrato->update(['status' => 'distratado']);

        $this->assertSame('disponivel', $unidade->fresh()->status);
    }

    public function test_distratado_cancela_titulos_abertos_e_mantem_recebidos(): void
    {
        $unidade = $this->criarUnidade();
        $contrato = $this->criarContrato(['unidade_id' => $unidade->id]);

        $aberta = ContaReceber::create([
            'contrato_venda_id' => $contrato->id,
            'cliente_id' => $contrato->cliente_id,
            'descricao' => 'Parcela 1/12 - ' . $contrato->numero,
            'valor' => 1000,
            'data_vencimento' => now()->addMonth()->toDateString(),
            'status' => 'aberto',
        ]);
        $vencida = ContaReceber::create([
            'contrato_venda_id' => $contrato->id,
            'cliente_id' => $contrato->cliente_id,
            'descricao' => 'Parcela 2/12 - ' . $contrato->numero,
            'valor' => 1000,
            'data_vencimento' => now()->subDay()->toDateString(),
            'status' => 'vencido',
        ]);
        $recebida = ContaReceber::create([
            'contrato_venda_id' => $contrato->id,
            'cliente_id' => $contrato->cliente_id,
            'descricao' => 'Parcela 0/12 - ' . $contrato->numero,
            'valor' => 1000,
            'valor_recebido' => 1000,
            'data_vencimento' => now()->subMonth()->toDateString(),
            'status' => 'recebido',
        ]);

        $contrato->update(['status' => 'distratado']);

        $this->assertSame('cancelado', $aberta->fresh()->status);
        $this->assertSame('cancelado', $vencida->fresh()->status);
        $this->assertSame('recebido', $recebida->fresh()->status);
        $this->assertSame('disponivel', $unidade->fresh()->status);
    }

    public function test_alterar_valor_venda_recalcula_comissao(): void
    {
        $contrato = $this->criarContrato(['valor_venda' => 100000]);

        $contrato->update(['valor_venda' => 150000]);

        $this->assertSame('6750.00', $contrato->fresh()->valor_comissao);
    }

    private function criarContrato(array $attrs = []): ContratoVenda
    {
        return ContratoVenda::create(array_merge([
            'numero' => ContratoVenda::gerarNumero(),
            'unidade_id' => $this->criarUnidade()->id,
            'cliente_id' => $this->criarCliente()->id,
            'status' => 'ativo',
            'valor_venda' => 200000,
            'data_contrato' => now()->toDateString(),
        ], $attrs));
    }
}
