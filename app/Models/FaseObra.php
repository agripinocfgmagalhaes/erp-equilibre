<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FaseObra extends Model
{
    protected $table = 'fases_obra';
    protected $fillable = ['projeto_id','nome','ordem','percentual'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
}
