<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FaseObra extends Model
{
    protected $table = 'fases_obra';
    protected $fillable = ['projeto_id','fase_padrao_id','nome','ordem','percentual','peso'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function fasePadrao(): BelongsTo { return $this->belongsTo(FasePadrao::class); }
}
