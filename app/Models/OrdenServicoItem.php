<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OrdenServicoItem extends Model
{
    protected $table = 'ordem_servico_itens';
    protected $fillable = ['ordem_servico_id', 'orcamento_item_id', 'servico_id', 'descricao', 'unidade', 'quantidade_contratada', 'valor_unitario', 'valor_total'];
    protected $casts = ['quantidade_contratada' => 'decimal:2', 'valor_unitario' => 'decimal:2', 'valor_total' => 'decimal:2'];
    public function ordemServico(): BelongsTo { return $this->belongsTo(OrdenServico::class, 'ordem_servico_id'); }
    public function orcamentoItem(): BelongsTo { return $this->belongsTo(OrcamentoItem::class); }
    public function servico(): BelongsTo { return $this->belongsTo(Servico::class); }
    public function medicaoItens(): HasMany { return $this->hasMany(MedicaoItem::class); }
    public function quantidadeMedidaAcumulada(): float
    {
        return (float) ($this->medicaoItens()->max('quantidade_acumulada') ?? 0);
    }
    protected static function booted(): void
    {
        static::saving(function (OrdenServicoItem $item) {
            $item->valor_total = (float) $item->quantidade_contratada * (float) $item->valor_unitario;
        });
        static::saved(function (OrdenServicoItem $item) {
            $item->ordemServico->update(['valor_total' => $item->ordemServico->itens()->sum('valor_total')]);
        });
        static::deleted(function (OrdenServicoItem $item) {
            $item->ordemServico->update(['valor_total' => $item->ordemServico->itens()->sum('valor_total')]);
        });
    }
}
