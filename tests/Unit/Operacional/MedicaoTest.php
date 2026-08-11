<?php

namespace Tests\Unit\Operacional;

use App\Models\ContaPagar;
use App\Models\Medicao;
use App\Models\OrdenServico;
use App\Models\Projeto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicaoTest extends TestCase
{
    use RefreshDatabase;

    private function criarOs(array $attrs = []): OrdenServico
    {
        $projeto = Projeto::create(['nome' => 'Residencial Equilíbre']);

        return OrdenServico::create(array_merge([
            'numero' => 'OS-'.uniqid(),
            'data' => now()->toDateString(),
            'projeto_id' => $projeto->id,
            'valor_total' => 1000,
            'status' => 'em_execucao',
        ], $attrs));
    }

    private function criarMedicao(OrdenServico $os, array $attrs = []): Medicao
    {
        return Medicao::create(array_merge([
            'ordem_servico_id' => $os->id,
            'numero' => 1,
            'data_medicao' => now()->toDateString(),
            'data_inicio_periodo' => now()->subDays(30)->toDateString(),
            'data_fim_periodo' => now()->toDateString(),
            'valor_total' => 500,
            'status' => 'medida',
        ], $attrs));
    }

    public function test_aprovar_gera_conta_a_pagar_faturada(): void
    {
        $os = $this->criarOs();
        $medicao = $this->criarMedicao($os);

        $medicao->aprovarEGerarContaPagar();

        $conta = ContaPagar::where('ordem_servico_id', $os->id)->first();
        $this->assertNotNull($conta);
        $this->assertSame('aberto', $conta->status);
        $this->assertSame('500.00', $conta->valor);
        $this->assertSame('faturada', $medicao->fresh()->status);
        $this->assertSame($conta->id, $medicao->fresh()->conta_pagar_id);
        $this->assertNotNull($medicao->fresh()->data_aprovacao);
    }

    public function test_percentual_acumulado_eh_calculado(): void
    {
        $os = $this->criarOs();
        $medicao = $this->criarMedicao($os);

        $medicao->aprovarEGerarContaPagar();

        $this->assertSame('50.00', $medicao->fresh()->percentual_acumulado);
    }

    public function test_pagar_conta_da_medicao_marca_medicao_paga(): void
    {
        $os = $this->criarOs();
        $medicao = $this->criarMedicao($os);
        $medicao->aprovarEGerarContaPagar();

        $conta = ContaPagar::where('ordem_servico_id', $os->id)->first();
        $conta->darBaixa(500, now()->toDateString());

        $this->assertSame('pago', $conta->fresh()->status);
        $this->assertSame('paga', $medicao->fresh()->status);
    }
}
