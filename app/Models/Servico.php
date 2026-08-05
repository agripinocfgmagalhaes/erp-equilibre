<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Servico extends Model
{
    protected $table = 'servicos';
    protected $fillable = ['nome', 'unidade_padrao', 'ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
