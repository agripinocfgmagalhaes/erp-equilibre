<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Cliente extends Model
{
    protected $fillable = ['nome','cpf','email','telefone','celular','endereco','bairro','cidade','estado','cep','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
