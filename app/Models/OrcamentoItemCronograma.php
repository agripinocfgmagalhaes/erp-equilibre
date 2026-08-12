<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrcamentoItemCronograma extends Model
{
    protected $table = 'orcamento_item_cronogramas';
    protected $fillable = ['orcamento_item_id', 'mes', 'percentual'];
    protected $casts = [
        'mes' => 'date',
        'percentual' => 'decimal:2',
    ];
    public function orcamentoItem(): BelongsTo
    {
        return $this->belongsTo(OrcamentoItem::class);
    }
}
