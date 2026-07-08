<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Projeto extends Model
{
    protected $table = 'projetos';
    protected $fillable = ['nome','descricao','status','data_inicio','data_previsao_fim'];
    public function unidades(): HasMany { return $this->hasMany(Unidade::class); }
    public function fasesObra(): HasMany { return $this->hasMany(FaseObra::class); }
}
