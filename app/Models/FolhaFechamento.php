<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
class FolhaFechamento extends Model
{
    protected $table = 'folha_fechamentos';
    protected $fillable = ['funcionario_id','periodo_inicio','periodo_fim','dias_trabalhados','valor_diaria','valor_total','status','conta_pagar_id'];
    protected $casts = ['periodo_inicio' => 'date', 'periodo_fim' => 'date', 'dias_trabalhados' => 'decimal:1', 'valor_diaria' => 'decimal:2', 'valor_total' => 'decimal:2'];

    public function funcionario(): BelongsTo { return $this->belongsTo(Funcionario::class); }
    public function contaPagar(): BelongsTo { return $this->belongsTo(ContaPagar::class); }

    public static function gerar(Funcionario $funcionario, string $periodoInicio, string $periodoFim): self
    {
        $diasTrabalhados = $funcionario->presencas()
            ->whereBetween('data', [$periodoInicio, $periodoFim])
            ->get()
            ->sum(fn ($p) => $p->fracao);

        $valorDiaria = (float) optional($funcionario->diariaAtual())->valor_diaria;
        $valorTotal = round($diasTrabalhados * $valorDiaria, 2);

        $fechamento = self::create([
            'funcionario_id' => $funcionario->id,
            'periodo_inicio' => $periodoInicio,
            'periodo_fim' => $periodoFim,
            'dias_trabalhados' => $diasTrabalhados,
            'valor_diaria' => $valorDiaria,
            'valor_total' => $valorTotal,
            'status' => 'fechado',
        ]);

        $contaPagar = ContaPagar::create([
            'descricao' => 'Folha - '.$funcionario->nome.' - '.Carbon::parse($periodoInicio)->format('d/m').' a '.Carbon::parse($periodoFim)->format('d/m/Y'),
            'contato_tipo' => 'funcionario',
            'contato_id' => $funcionario->id,
            'projeto_id' => $funcionario->projeto_id,
            'valor' => $valorTotal,
            'valor_pago' => 0,
            'status' => 'aberto',
            'data_vencimento' => Carbon::parse($periodoFim)->addDays(5)->toDateString(),
        ]);

        $fechamento->update(['conta_pagar_id' => $contaPagar->id]);

        return $fechamento;
    }
}
