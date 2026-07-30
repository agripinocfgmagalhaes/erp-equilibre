<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisicaoCompra extends Model
{
    protected $table = 'requisicoes_compra';
    protected $fillable = [
        'numero', 'projeto_id', 'fase_obra_id', 'solicitante_id', 'aprovador_id',
        'status', 'data_requisicao', 'justificativa', 'motivo_reprovacao',
        'data_aprovacao', 'observacoes',
    ];
    protected $casts = [
        'data_requisicao' => 'date',
        'data_aprovacao' => 'datetime',
    ];

    public const MAX_COTACOES = 3;

    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function faseObra(): BelongsTo { return $this->belongsTo(FaseObra::class); }
    public function solicitante(): BelongsTo { return $this->belongsTo(User::class, 'solicitante_id'); }
    public function aprovador(): BelongsTo { return $this->belongsTo(User::class, 'aprovador_id'); }
    public function itens(): HasMany { return $this->hasMany(ItemRequisicaoCompra::class); }
    public function cotacoes(): HasMany { return $this->hasMany(CotacaoCompra::class); }
    public function pedidoCompra() { return $this->hasOne(PedidoCompra::class); }

    public static function gerarNumero(): string
    {
        $ano = now()->year;
        $ultimo = static::where('numero', 'like', "RC-{$ano}-%")->count();
        return sprintf('RC-%d-%04d', $ano, $ultimo + 1);
    }

    public function enviarParaAprovacao(): void
    {
        $this->update(['status' => 'pendente_aprovacao']);
    }

    public function aprovar(User $aprovador): void
    {
        $this->update([
            'status' => 'em_cotacao',
            'aprovador_id' => $aprovador->id,
            'data_aprovacao' => now(),
        ]);
    }

    public function reprovar(User $aprovador, string $motivo): void
    {
        $this->update([
            'status' => 'reprovada',
            'aprovador_id' => $aprovador->id,
            'data_aprovacao' => now(),
            'motivo_reprovacao' => $motivo,
        ]);
    }

    public function podeReceberCotacao(): bool
    {
        return in_array($this->status, ['em_cotacao', 'cotada'], true)
            && $this->cotacoes()->count() < self::MAX_COTACOES;
    }

    public function selecionarVencedoraEGerarPedido(CotacaoCompra $cotacao): PedidoCompra
    {
        $this->cotacoes()->update(['vencedora' => false]);
        $cotacao->update(['vencedora' => true]);

        $pedido = PedidoCompra::create([
            'numero' => PedidoCompra::gerarNumero(),
            'requisicao_compra_id' => $this->id,
            'cotacao_compra_id' => $cotacao->id,
            'projeto_id' => $this->projeto_id,
            'fase_obra_id' => $this->fase_obra_id,
            'fornecedor_id' => $cotacao->fornecedor_id,
            'status' => 'aprovado',
            'data_pedido' => now(),
            'data_previsao_entrega' => $cotacao->prazo_entrega_dias
                ? now()->addDays($cotacao->prazo_entrega_dias)
                : null,
            'observacoes' => 'Gerado a partir da Requisição '.$this->numero,
        ]);

        foreach ($cotacao->itens as $itemCotacao) {
            $itemReq = $itemCotacao->itemRequisicao;
            ItemPedidoCompra::create([
                'pedido_compra_id' => $pedido->id,
                'produto_id' => $itemReq?->produto_id,
                'descricao' => $itemReq?->descricao,
                'unidade' => $itemReq?->unidade ?? 'UN',
                'quantidade' => $itemReq?->quantidade ?? 1,
                'valor_unitario' => $itemCotacao->valor_unitario,
            ]);
        }

        $this->update(['status' => 'pedido_gerado']);

        return $pedido;
    }
}
