<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PedidoCompra extends Model
{
    protected $table = 'pedidos_compra';
    protected $fillable = ['numero','requisicao_compra_id','cotacao_compra_id','projeto_id','fase_obra_id','fornecedor_id','status','data_pedido','data_previsao_entrega','valor_total','observacoes'];
    protected $casts = ['data_pedido' => 'date', 'data_previsao_entrega' => 'date', 'valor_total' => 'decimal:2'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function faseObra(): BelongsTo { return $this->belongsTo(FaseObra::class); }
    public function fornecedor(): BelongsTo { return $this->belongsTo(Fornecedor::class); }
    public function requisicaoCompra(): BelongsTo { return $this->belongsTo(RequisicaoCompra::class); }
    public function cotacaoCompra(): BelongsTo { return $this->belongsTo(CotacaoCompra::class); }
    public function itens(): HasMany { return $this->hasMany(ItemPedidoCompra::class); }
    public function recalcularTotal(): void { $this->update(['valor_total' => $this->itens()->sum('valor_total')]); }
    public static function gerarNumero(): string
    {
        $ano = now()->year;
        $ultimo = static::where('numero', 'like', "PC-{$ano}-%")->count();
        return sprintf('PC-%d-%04d', $ano, $ultimo + 1);
    }
}
