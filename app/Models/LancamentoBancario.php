<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LancamentoBancario extends Model
{
    protected $table = 'lancamentos_bancarios';
    protected $fillable = ['conta_bancaria_id','tipo','descricao','valor','data','origem','origem_id','observacoes'];
    protected $casts = ['valor' => 'decimal:2', 'data' => 'date'];
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public static function registrarBaixa(string $origem, int $origemId, int $contaBancariaId, string $tipo, string $descricao, float $valor, string $data): void
    {
        static::where('origem', $origem)->where('origem_id', $origemId)->delete();
        static::create(['conta_bancaria_id' => $contaBancariaId, 'tipo' => $tipo, 'descricao' => $descricao, 'valor' => $valor, 'data' => $data, 'origem' => $origem, 'origem_id' => $origemId]);
    }
}
