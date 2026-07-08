<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Prestador extends Model
{
    protected $table = 'prestadores';
    protected $fillable = ['nome','cpf_cnpj','email','telefone','especialidade','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
