<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LancamentoBancario extends Model
{
    protected $table = 'lancamentos_bancarios';
    protected $fillable = ['conta_bancaria_id','tipo','descricao','valor','data','origem','origem_id','transferencia_grupo','observacoes','conciliado','conciliado_em','conciliado_por','inter_transacao_id'];
    protected $casts = ['valor' => 'decimal:2', 'data' => 'date'];
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public static function registrarTransferencia(int $contaOrigemId, int $contaDestinoId, float $valor, string $data, string $descricao = ''): void
    {
        $grupo = (string) \Illuminate\Support\Str::uuid();
        $desc = $descricao !== '' ? $descricao : 'Transferência entre contas';
        static::create(['conta_bancaria_id' => $contaOrigemId, 'tipo' => 'saida', 'descricao' => $desc, 'valor' => $valor, 'data' => $data, 'origem' => 'transferencia', 'transferencia_grupo' => $grupo]);
        static::create(['conta_bancaria_id' => $contaDestinoId, 'tipo' => 'entrada', 'descricao' => $desc, 'valor' => $valor, 'data' => $data, 'origem' => 'transferencia', 'transferencia_grupo' => $grupo]);
    }

    public static function registrarBaixa(string $origem, int $origemId, int $contaBancariaId, string $tipo, string $descricao, float $valor, string $data): void
    {
        static::where('origem', $origem)->where('origem_id', $origemId)->delete();
        static::create(['conta_bancaria_id' => $contaBancariaId, 'tipo' => $tipo, 'descricao' => $descricao, 'valor' => $valor, 'data' => $data, 'origem' => $origem, 'origem_id' => $origemId]);
    }
}
