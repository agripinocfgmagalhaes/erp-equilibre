<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Corretor extends Model
{
    protected $table = 'corretores';
    protected $fillable = ['nome','imobiliaria_id','cpf_cnpj','creci','email','telefone','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
    public function imobiliaria(): BelongsTo { return $this->belongsTo(Imobiliaria::class); }
}