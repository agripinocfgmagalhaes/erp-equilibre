<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unidade extends Model
{
    protected $table = 'unidades';

    protected $fillable = ['projeto_id','identificacao','tipo','area','valor_tabela','valor_avaliado','status','andar','tipologia','vaga_garagem'];

    protected $casts = ['valor_tabela' => 'decimal:2','valor_avaliado' => 'decimal:2'];

    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
}
