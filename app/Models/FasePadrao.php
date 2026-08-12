<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FasePadrao extends Model
{
    protected $table = 'fases_padrao';
    protected $fillable = ['nome','macro_categoria','ordem'];

    protected static function booted(): void
    {
        static::creating(function (FasePadrao $fase) {
            if (empty($fase->descricao)) {
                $fase->descricao = $fase->nome;
            }
            if ($fase->ativo === null) {
                $fase->ativo = true;
            }
            if (empty($fase->numero)) {
                $max = (int) static::whereRaw("numero REGEXP '^[0-9]+$'")->max('numero');
                $fase->numero = (string) ($max + 1);
            }
            if (empty($fase->macro_categoria)) {
                $n = mb_strtoupper($fase->nome ?? '');
                $fase->macro_categoria = match (true) {
                    str_contains($n, 'PRELIMINAR'), str_contains($n, 'AUXILIAR'), str_contains($n, 'TERRA'), str_contains($n, 'FUNDA') => 'Preparação e Infraestrutura',
                    str_contains($n, 'ESTRUTURA'), str_contains($n, 'PAREDES'), str_contains($n, 'COBERT'), str_contains($n, 'IMPERMEAB') => 'Superestrutura',
                    str_contains($n, 'INSTAL') => 'Instalações',
                    str_contains($n, 'ACABAMENT'), str_contains($n, 'REVEST'), str_contains($n, 'GESSO'), str_contains($n, 'BANCADAS'), str_contains($n, 'ESQUADRI'), str_contains($n, 'PINTURA') => 'Acabamentos Internos',
                    str_contains($n, 'PISO'), str_contains($n, 'EXTERN'), str_contains($n, 'ÁREA') => 'Acabamentos Externos e Áreas Comuns',
                    default => 'Diversos',
                };
            }
        });
    }
}
