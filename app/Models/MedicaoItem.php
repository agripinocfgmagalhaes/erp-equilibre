<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MedicaoItem extends Model
{
    protected $table = 'medicao_itens';
    protected $fillable = ['medicao_id', 'ordem_servico_item_id', 'quantidade_periodo', 'quantidade_acumulada', 'valor_total'];
    protected $casts = ['quantidade_periodo' => 'decimal:2', 'quantidade_acumulada' => 'decimal:2', 'valor_total' => 'decimal:2'];
    public function medicao(): BelongsTo { return $this->belongsTo(Medicao::class); }
    public function ordemServicoItem(): BelongsTo { return $this->belongsTo(OrdenServicoItem::class); }
    protected static function booted(): void
    {
        static::saving(function (MedicaoItem $item) {
            $item->valor_total = (float) $item->quantidade_periodo * (float) $item->ordemServicoItem->valor_unitario;
            $anteriorAcumulado = static::where('ordem_servico_item_id', $item->ordem_servico_item_id)
                ->where('id', '!=', $item->id ?? 0)
                ->orderByDesc('id')->value('quantidade_acumulada') ?? 0;
            $item->quantidade_acumulada = (float) $anteriorAcumulado + (float) $item->quantidade_periodo;
        });
        static::saved(function (MedicaoItem $item) {
            $item->medicao->update(['valor_total' => $item->medicao->itens()->sum('valor_total')]);
        });
        static::deleted(function (MedicaoItem $item) {
            $item->medicao->update(['valor_total' => $item->medicao->itens()->sum('valor_total')]);
        });
    }
}
