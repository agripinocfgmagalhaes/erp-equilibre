<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Corretor extends Model
{
    protected $table = 'corretores';
    protected $fillable = ['nome','cpf_cnpj','creci','email','telefone','celular','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
