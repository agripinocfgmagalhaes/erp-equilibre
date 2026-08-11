<?php

namespace Tests\Unit\Compras;

use App\Models\CotacaoCompra;
use App\Models\Fornecedor;
use App\Models\ItemCotacaoCompra;
use App\Models\ItemRequisicaoCompra;
use App\Models\Produto;
use App\Models\RequisicaoCompra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisicaoCompraTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuario(): User
    {
        return User::create(['name' => 'Usuário Teste', 'email' => 'user'.uniqid().'@teste.com', 'password' => 'secret123']);
    }

    private function criarRequisicao(array $attrs = []): RequisicaoCompra
    {
        return RequisicaoCompra::create(array_merge([
            'numero' => RequisicaoCompra::gerarNumero(),
            'solicitante_id' => $this->criarUsuario()->id,
            'data_requisicao' => now()->toDateString(),
            'status' => 'rascunho',
        ], $attrs));
    }

    private function criarItem(RequisicaoCompra $requisicao, array $attrs = []): ItemRequisicaoCompra
    {
        return ItemRequisicaoCompra::create(array_merge([
            'requisicao_compra_id' => $requisicao->id,
            'descricao' => 'Cimento 50kg',
            'quantidade' => 10,
        ], $attrs));
    }

    public function test_numero_eh_gerado_sequencialmente(): void
    {
        $ano = now()->year;
        $requisicao = $this->criarRequisicao();

        $this->assertMatchesRegularExpression("/^RC-{$ano}-\d{4}$/", $requisicao->numero);
    }

    public function test_enviar_para_aprovacao_muda_status(): void
    {
        $requisicao = $this->criarRequisicao();

        $requisicao->enviarParaAprovacao();

        $this->assertSame('pendente_aprovacao', $requisicao->fresh()->status);
    }

    public function test_aprovar_registra_aprovador_e_vai_para_cotacao(): void
    {
        $requisicao = $this->criarRequisicao(['status' => 'pendente_aprovacao']);
        $aprovador = $this->criarUsuario();

        $requisicao->aprovar($aprovador);

        $this->assertSame('em_cotacao', $requisicao->fresh()->status);
        $this->assertSame($aprovador->id, $requisicao->fresh()->aprovador_id);
        $this->assertNotNull($requisicao->fresh()->data_aprovacao);
    }

    public function test_reprovar_registra_motivo(): void
    {
        $requisicao = $this->criarRequisicao(['status' => 'pendente_aprovacao']);
        $aprovador = $this->criarUsuario();

        $requisicao->reprovar($aprovador, 'Fora do orçamento');

        $this->assertSame('reprovada', $requisicao->fresh()->status);
        $this->assertSame('Fora do orçamento', $requisicao->fresh()->motivo_reprovacao);
    }

    public function test_aceita_no_maximo_tres_cotacoes(): void
    {
        $requisicao = $this->criarRequisicao(['status' => 'em_cotacao']);
        $fornecedor = Fornecedor::create(['nome' => 'Fornecedor A', 'ativo' => true]);

        for ($i = 0; $i < 3; $i++) {
            CotacaoCompra::create([
                'requisicao_compra_id' => $requisicao->id,
                'fornecedor_id' => $fornecedor->id,
                'data_cotacao' => now()->toDateString(),
            ]);
        }

        $this->assertFalse($requisicao->fresh()->podeReceberCotacao());
    }

    public function test_gerar_pedido_direto_cria_pedido_aprovado(): void
    {
        $requisicao = $this->criarRequisicao(['status' => 'em_cotacao']);
        $item = $this->criarItem($requisicao);
        $produto = Produto::create(['nome' => 'Cimento', 'ativo' => true]);
        $item->update(['produto_id' => $produto->id]);
        $fornecedor = Fornecedor::create(['nome' => 'Fornecedor B', 'ativo' => true]);

        $pedido = $requisicao->gerarPedidoDireto($fornecedor->id, [$item->id => 32.50]);

        $this->assertSame('pedido_gerado', $requisicao->fresh()->status);
        $this->assertSame('aprovado', $pedido->status);
        $this->assertSame($fornecedor->id, $pedido->fornecedor_id);
        $this->assertSame($requisicao->id, $pedido->requisicao_compra_id);
        $this->assertCount(1, $pedido->itens);
        $this->assertSame('325.00', $pedido->fresh()->valor_total);
    }

    public function test_selecionar_vencedora_gera_pedido_da_cotacao(): void
    {
        $requisicao = $this->criarRequisicao(['status' => 'cotada']);
        $item = $this->criarItem($requisicao);
        $produto = Produto::create(['nome' => 'Cimento', 'ativo' => true]);
        $item->update(['produto_id' => $produto->id]);
        $fornecedor = Fornecedor::create(['nome' => 'Fornecedor C', 'ativo' => true]);

        $cotacao = CotacaoCompra::create([
            'requisicao_compra_id' => $requisicao->id,
            'fornecedor_id' => $fornecedor->id,
            'data_cotacao' => now()->toDateString(),
            'prazo_entrega_dias' => 15,
        ]);
        ItemCotacaoCompra::create([
            'cotacao_compra_id' => $cotacao->id,
            'item_requisicao_compra_id' => $item->id,
            'valor_unitario' => 31.90,
        ]);

        $pedido = $requisicao->selecionarVencedoraEGerarPedido($cotacao);

        $this->assertSame('pedido_gerado', $requisicao->fresh()->status);
        $this->assertTrue($cotacao->fresh()->vencedora);
        $this->assertSame($cotacao->id, $pedido->cotacao_compra_id);
        $this->assertCount(1, $pedido->itens);
        $this->assertSame('319.00', $pedido->fresh()->valor_total);
    }
}
