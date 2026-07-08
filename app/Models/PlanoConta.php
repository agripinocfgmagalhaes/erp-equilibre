<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PlanoConta extends Model
{
    protected $table = 'plano_contas';
    protected $fillable = ['codigo','nome','tipo','plano_conta_pai_id','ativo'];
    protected $casts = ['ativo' => 'boolean'];
    public function pai(): BelongsTo { return $this->belongsTo(PlanoConta::class, 'plano_conta_pai_id'); }
    public function filhos(): HasMany { return $this->hasMany(PlanoConta::class, 'plano_conta_pai_id'); }
}
