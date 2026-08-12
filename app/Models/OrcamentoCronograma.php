<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoCronograma extends Model
{
    protected $fillable = ['orcamento_id', 'codigo_item', 'competencia', 'percentual'];
    protected $casts = ['competencia' => 'date', 'percentual' => 'decimal:2'];

    public function orcamento(): BelongsTo { return $this->belongsTo(Orcamento::class); }
}
