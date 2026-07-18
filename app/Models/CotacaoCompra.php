<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CotacaoCompra extends Model
{
    protected $table = 'cotacoes_compra';
    protected $fillable = [
        'requisicao_compra_id', 'fornecedor_id', 'data_cotacao', 'prazo_entrega_dias',
        'condicao_pagamento', 'valor_total', 'arquivo_path', 'vencedora', 'observacoes',
    ];
    protected $casts = [
        'data_cotacao' => 'date',
        'valor_total' => 'decimal:2',
        'vencedora' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (CotacaoCompra $cotacao) {
            if ($cotacao->requisicaoCompra && $cotacao->requisicaoCompra->status === 'em_cotacao') {
                $cotacao->requisicaoCompra->update(['status' => 'cotada']);
            }
        });
    }

    public function requisicaoCompra(): BelongsTo { return $this->belongsTo(RequisicaoCompra::class); }
    public function fornecedor(): BelongsTo { return $this->belongsTo(Fornecedor::class); }
    public function itens(): HasMany { return $this->hasMany(ItemCotacaoCompra::class); }

    public function recalcularTotal(): void
    {
        $this->update(['valor_total' => $this->itens()->sum('valor_total')]);
    }
}
