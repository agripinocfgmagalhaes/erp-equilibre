<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Imobiliaria extends Model
{
    protected $table = 'imobiliarias';
    protected $fillable = ['nome', 'creci', 'telefone'];
    public function corretores(): HasMany { return $this->hasMany(Corretor::class); }
}
