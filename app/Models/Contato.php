<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Contato extends Model
{
    protected $table = 'view_contatos';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'uid';
    protected $keyType = 'string';
    protected $fillable = [];
    public static function optionsParaSelect(): array
    {
        return static::orderBy('nome')->get()->mapWithKeys(function ($c) {
            $label = ucfirst($c->contato_tipo).' - '.$c->nome;
            if ($c->cpf_cnpj) $label .= ' ('.$c->cpf_cnpj.')';
            return [$c->contato_tipo.'|'.$c->contato_id => $label];
        })->toArray();
    }
}
