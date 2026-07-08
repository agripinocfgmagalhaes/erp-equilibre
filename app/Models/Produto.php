<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Produto extends Model
{
    protected $table = 'produtos';
    protected $fillable = ['codigo','nome','unidade','categoria','preco_referencia','ativo'];
    protected $casts = ['preco_referencia' => 'decimal:2', 'ativo' => 'boolean'];
}
