<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Prestador extends Model
{
    protected $table = 'prestadores';
    protected $fillable = ['nome','cpf_cnpj','email','telefone','chave_pix','tipo_chave_pix','especialidade','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
