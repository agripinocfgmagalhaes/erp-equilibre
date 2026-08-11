<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Balao extends Model
{
    protected $table = 'baloes';
    protected $fillable = ['contrato_venda_id', 'ordem', 'descricao', 'valor', 'data_vencimento'];
    protected $casts = ['valor' => 'decimal:2', 'data_vencimento' => 'date'];

    public function contratoVenda(): BelongsTo
    {
        return $this->belongsTo(ContratoVenda::class);
    }
}
