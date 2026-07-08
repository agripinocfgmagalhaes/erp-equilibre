<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ItemPedidoCompra extends Model
{
    protected $table = 'itens_pedido_compra';
    protected $fillable = ['pedido_compra_id','produto_id','descricao','unidade','quantidade','valor_unitario','valor_total'];
    protected $casts = ['quantidade' => 'decimal:2', 'valor_unitario' => 'decimal:2', 'valor_total' => 'decimal:2'];
    protected static function booted(): void
    {
        static::saving(function (ItemPedidoCompra $item) {
            $item->valor_total = round($item->quantidade * $item->valor_unitario, 2);
        });
        static::saved(fn (ItemPedidoCompra $item) => $item->pedidoCompra?->recalcularTotal());
        static::deleted(fn (ItemPedidoCompra $item) => $item->pedidoCompra?->recalcularTotal());
    }
    public function pedidoCompra(): BelongsTo { return $this->belongsTo(PedidoCompra::class); }
    public function produto(): BelongsTo { return $this->belongsTo(Produto::class); }
}
