<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Fornecedor extends Model
{
    protected $table = 'fornecedores';
    protected $fillable = ['nome','cnpj','email','telefone','contato','observacoes','ativo'];
    protected $casts = ['ativo' => 'boolean'];
}
