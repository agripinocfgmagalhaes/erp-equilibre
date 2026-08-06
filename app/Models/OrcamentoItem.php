<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrcamentoItem extends Model
{
    protected $table = 'orcamento_itens';
    protected $fillable = ['projeto_id', 'fase_padrao_id', 'servico_id', 'descricao', 'unidade', 'quantidade', 'valor_unitario', 'valor_total'];
    protected $casts = ['quantidade' => 'decimal:2', 'valor_unitario' => 'decimal:2', 'valor_total' => 'decimal:2'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function fasePadrao(): BelongsTo { return $this->belongsTo(FasePadrao::class, 'fase_padrao_id'); }
    public function servico(): BelongsTo { return $this->belongsTo(Servico::class); }
    protected static function booted(): void
    {
        static::saving(function (OrcamentoItem $item) {
            $item->valor_total = (float) $item->quantidade * (float) $item->valor_unitario;
        });
    }
}
