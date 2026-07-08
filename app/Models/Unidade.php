<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Unidade extends Model
{
    protected $table = 'unidades';
    protected $fillable = ['projeto_id','identificacao','tipo','area','valor_tabela','status'];
    protected $casts = ['valor_tabela' => 'decimal:2'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
}
