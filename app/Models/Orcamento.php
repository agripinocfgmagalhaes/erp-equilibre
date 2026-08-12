<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Orcamento extends Model
{
    protected $fillable = ['projeto_id', 'nome', 'data_base', 'area_construida', 'numero_unidades', 'status', 'observacoes'];
    protected $casts = ['data_base' => 'date', 'area_construida' => 'decimal:2'];

    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function itens(): HasMany { return $this->hasMany(OrcamentoItem::class); }
    public function cronograma(): HasMany { return $this->hasMany(OrcamentoCronograma::class); }

    public function totalGeral(): float
    {
        return (float) $this->itens()->where('tipo', 'item')->sum(DB::raw('quantidade * custo_unitario'));
    }

    public function totalPorClassificacao(string $c): float
    {
        return (float) $this->itens()->where('tipo', 'item')->where('classificacao', $c)->sum(DB::raw('quantidade * custo_unitario'));
    }
}
