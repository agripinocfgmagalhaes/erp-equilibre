<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemRequisicaoCompra extends Model
{
    protected $table = 'itens_requisicao_compra';
    protected $fillable = ['requisicao_compra_id', 'produto_id', 'descricao', 'unidade', 'quantidade'];
    protected $casts = ['quantidade' => 'decimal:2'];

    public function requisicaoCompra(): BelongsTo { return $this->belongsTo(RequisicaoCompra::class); }
    public function produto(): BelongsTo { return $this->belongsTo(Produto::class); }
    public function itensCotacao(): HasMany { return $this->hasMany(ItemCotacaoCompra::class, 'item_requisicao_compra_id'); }
}
