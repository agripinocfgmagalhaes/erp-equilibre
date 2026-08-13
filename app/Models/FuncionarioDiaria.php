<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FuncionarioDiaria extends Model
{
    protected $table = 'funcionario_diarias';
    protected $fillable = ['funcionario_id','valor_diaria','vigente_desde','vigente_ate'];
    protected $casts = ['valor_diaria' => 'decimal:2', 'vigente_desde' => 'date', 'vigente_ate' => 'date'];
    public function funcionario(): BelongsTo { return $this->belongsTo(Funcionario::class); }
}
