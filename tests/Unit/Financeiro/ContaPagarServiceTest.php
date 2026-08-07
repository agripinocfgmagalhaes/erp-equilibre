<?php

namespace Tests\Unit\Financeiro;

use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Services\Financeiro\ContaPagarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ContaPagarServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContaPagarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ContaPagarService::class);
    }

    private function criarConta(array $attrs = []): ContaPagar
    {
        return ContaPagar::create(array_merge([
            'descricao' => 'NF 123',
            'valor' => 1000,
            'data_vencimento' => now()->addDays(7)->toDateString(),
            'status' => 'aberto',
        ], $attrs));
    }

    public function test_rejeita_valor_zero_ou_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarConta(), 0);
    }

    public function test_rejeita_valor_acima_do_titulo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarConta(), 1000.01);
    }

    public function test_rejeita_titulo_ja_pago(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarConta(['status' => 'pago']), 1000);
    }

    public function test_baixa_total_marca_pago_e_gera_lancamento(): void
    {
        $banco = ContaBancaria::create(['nome' => 'Inter', 'banco' => 'Inter', 'agencia' => '0001', 'conta' => '123456', 'saldo' => 5000, 'ativo' => true]);
        $conta = $this->criarConta();

        $this->service->darBaixa($conta, 1000, now()->toDateString(), $banco->id);

        $this->assertSame('pago', $conta->fresh()->status);
        $this->assertDatabaseHas('lancamentos_bancarios', ['origem' => 'conta_pagar', 'origem_id' => $conta->id]);
    }

    public function test_baixa_parcial_mantem_aberto(): void
    {
        $conta = $this->criarConta();

        $this->service->darBaixa($conta, 400);

        $this->assertSame('aberto', $conta->fresh()->status);
    }

    public function test_conta_vencida_recebe_status_vencido_ao_salvar(): void
    {
        $conta = $this->criarConta(['data_vencimento' => now()->subDay()->toDateString()]);

        $this->assertSame('vencido', $conta->fresh()->status);
    }
}
