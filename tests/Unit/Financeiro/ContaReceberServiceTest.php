<?php

namespace Tests\Unit\Financeiro;

use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Services\Financeiro\ContaReceberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ContaReceberServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContaReceberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ContaReceberService::class);
    }

    private function criarTitulo(array $attrs = []): ContaReceber
    {
        return ContaReceber::create(array_merge([
            'descricao' => 'Parcela 01',
            'valor' => 1000,
            'data_vencimento' => now()->addDays(7)->toDateString(),
            'status' => 'aberto',
        ], $attrs));
    }

    public function test_rejeita_valor_zero_ou_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarTitulo(), 0);
    }

    public function test_rejeita_valor_acima_do_titulo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarTitulo(), 1000.01);
    }

    public function test_rejeita_titulo_ja_recebido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarTitulo(['status' => 'recebido']), 1000);
    }

    public function test_rejeita_titulo_cancelado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarTitulo(['status' => 'cancelado']), 1000);
    }

    public function test_baixa_total_marca_recebido_e_gera_lancamento(): void
    {
        $banco = ContaBancaria::create(['nome' => 'Inter', 'banco' => 'Inter', 'agencia' => '0001', 'conta' => '123456', 'saldo_inicial' => 5000, 'ativo' => true]);
        $titulo = $this->criarTitulo();

        $this->service->darBaixa($titulo, 1000, now()->toDateString(), $banco->id);

        $this->assertSame('recebido', $titulo->fresh()->status);
        $this->assertDatabaseHas('lancamentos_bancarios', ['origem' => 'conta_receber', 'origem_id' => $titulo->id, 'tipo' => 'entrada']);
    }

    public function test_baixa_parcial_mantem_aberto(): void
    {
        $titulo = $this->criarTitulo();

        $this->service->darBaixa($titulo, 400);

        $this->assertSame('aberto', $titulo->fresh()->status);
        $this->assertSame('400.00', $titulo->fresh()->valor_recebido);
    }

    public function test_baixa_parcial_sobrescreve_valor_recebido(): void
    {
        $titulo = $this->criarTitulo();

        $this->service->darBaixa($titulo, 400);
        $this->service->darBaixa($titulo, 600);

        $this->assertSame('aberto', $titulo->fresh()->status);
        $this->assertSame('600.00', $titulo->fresh()->valor_recebido);
    }

    public function test_rejeita_conta_bancaria_inativa(): void
    {
        $banco = ContaBancaria::create(['nome' => 'Inter', 'banco' => 'Inter', 'agencia' => '0001', 'conta' => '123456', 'saldo_inicial' => 5000, 'ativo' => false]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->darBaixa($this->criarTitulo(), 1000, now()->toDateString(), $banco->id);
    }
}
