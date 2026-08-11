<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoItem extends Model
{
    protected $table = 'orcamento_itens';

    protected $fillable = [
        'projeto_id', 'fase_padrao_id', 'servico_id', 'descricao', 'unidade', 'quantidade',
        'material_unitario', 'mdo_unitario', 'outros_unitario',
        'material_total', 'mdo_total', 'outros_total',
        'valor_unitario', 'valor_total',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'material_unitario' => 'decimal:2',
        'mdo_unitario' => 'decimal:2',
        'outros_unitario' => 'decimal:2',
        'material_total' => 'decimal:2',
        'mdo_total' => 'decimal:2',
        'outros_total' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrcamentoItem $item) {
            $item->material_total = round(($item->quantidade ?? 0) * ($item->material_unitario ?? 0), 2);
            $item->mdo_total = round(($item->quantidade ?? 0) * ($item->mdo_unitario ?? 0), 2);
            $item->outros_total = round(($item->quantidade ?? 0) * ($item->outros_unitario ?? 0), 2);
            $item->valor_unitario = round(($item->material_unitario ?? 0) + ($item->mdo_unitario ?? 0) + ($item->outros_unitario ?? 0), 2);
            $item->valor_total = round($item->material_total + $item->mdo_total + $item->outros_total, 2);
        });
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function fasePadrao(): BelongsTo
    {
        return $this->belongsTo(FasePadrao::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
