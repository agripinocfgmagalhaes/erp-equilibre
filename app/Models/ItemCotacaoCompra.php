<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCotacaoCompra extends Model
{
    protected $table = 'itens_cotacao_compra';
    protected $fillable = ['cotacao_compra_id', 'item_requisicao_compra_id', 'valor_unitario', 'valor_total'];
    protected $casts = ['valor_unitario' => 'decimal:2', 'valor_total' => 'decimal:2'];

    protected static function booted(): void
    {
        static::saving(function (ItemCotacaoCompra $item) {
            $qtd = $item->itemRequisicao?->quantidade ?? 1;
            $item->valor_total = round($qtd * $item->valor_unitario, 2);
        });
        static::saved(fn (ItemCotacaoCompra $item) => $item->cotacaoCompra?->recalcularTotal());
        static::deleted(fn (ItemCotacaoCompra $item) => $item->cotacaoCompra?->recalcularTotal());
    }

    public function cotacaoCompra(): BelongsTo { return $this->belongsTo(CotacaoCompra::class); }
    public function itemRequisicao(): BelongsTo { return $this->belongsTo(ItemRequisicaoCompra::class, 'item_requisicao_compra_id'); }
}
