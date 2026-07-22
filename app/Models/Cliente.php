<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Cliente extends Model
{
    protected $fillable = [
        'nome', 'cpf', 'email', 'telefone', 'whatsapp',
        'renda_familiar', 'estado_civil', 'profissao',
        'conjuge_nome', 'conjuge_cpf', 'conjuge_renda',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'observacoes', 'ativo',
    ];
    protected $casts = [
        'ativo' => 'boolean',
        'renda_familiar' => 'decimal:2',
        'conjuge_renda' => 'decimal:2',
    ];

    public const ESTADOS_CIVIS = [
        'solteiro' => 'Solteiro(a)',
        'casado' => 'Casado(a)',
        'divorciado' => 'Divorciado(a)',
        'viuvo' => 'Viúvo(a)',
        'uniao_estavel' => 'União Estável',
    ];

    public function temConjuge(): bool
    {
        return in_array($this->estado_civil, ['casado', 'uniao_estavel'], true);
    }
}
