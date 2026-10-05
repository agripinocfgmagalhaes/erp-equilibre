<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FuncionarioPresenca extends Model
{
    protected $table = 'funcionario_presencas';
    protected $fillable = ['funcionario_id','data','status','observacao'];
    protected $casts = ['data' => 'date'];
    public function funcionario(): BelongsTo { return $this->belongsTo(Funcionario::class); }
    public function getFracaoAttribute(): float
    {
        return match ($this->status) {
            'integral' => 1.0,
            'meia_manha', 'meia_tarde' => 0.5,
            default => 0.0,
        };
    }
}
