<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoItem extends Model
{
    protected $table = 'orcamento_itens';
    protected $fillable = ['tipo', 'tipo', 'projeto_id', 'fase_padrao_id', 'servico_id', 'numero_item', 'ordem', 'descricao', 'unidade', 'quantidade', 'valor_unitario', 'material_unitario', 'mdo_unitario', 'outros_unitario', 'valor_total', 'material_total', 'mdo_total', 'outros_total'];
    protected $casts = ['quantidade' => 'decimal:2', 'valor_unitario' => 'decimal:2', 'material_unitario' => 'decimal:2', 'mdo_unitario' => 'decimal:2', 'outros_unitario' => 'decimal:2', 'valor_total' => 'decimal:2', 'material_total' => 'decimal:2', 'mdo_total' => 'decimal:2', 'outros_total' => 'decimal:2'];

    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function fasePadrao(): BelongsTo { return $this->belongsTo(FasePadrao::class); }
    public function servico(): BelongsTo { return $this->belongsTo(Servico::class); }

    public static function pluralName(): string
    {
        return "orcamento_itens";
    }

}
