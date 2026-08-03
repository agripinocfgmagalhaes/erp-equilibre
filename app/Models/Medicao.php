<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Medicao extends Model
{
    protected $table = 'medicoes';
    protected $fillable = ['ordem_servico_id','numero','data_medicao','data_inicio_periodo','data_fim_periodo','valor_total','percentual_acumulado','status','data_aprovacao','usuario_medicao_id','observacoes','conta_pagar_id'];
    protected $casts = ['valor_total' => 'decimal:2', 'percentual_acumulado' => 'decimal:2', 'data_medicao' => 'date', 'data_inicio_periodo' => 'date', 'data_fim_periodo' => 'date', 'data_aprovacao' => 'date'];
    public function ordemServico(): BelongsTo { return $this->belongsTo(OrdenServico::class); }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class, 'usuario_medicao_id'); }
    public function contaPagar(): BelongsTo { return $this->belongsTo(ContaPagar::class); }

    public function aprovarEGerarContaPagar(): void
    {
        $this->update(["status" => "aprovada", "data_aprovacao" => now()]);
        $contaPagar = ContaPagar::create([
            "descricao" => "Medição ".$this->numero." - OS ".$this->ordemServico->numero,
            "contato_tipo" => $this->ordemServico->prestador_id ? "prestador" : null,
            "contato_id" => $this->ordemServico->prestador_id,
            "projeto_id" => $this->ordemServico->projeto_id,
            "fase_obra_id" => $this->ordemServico->fase_obra_id,
            "valor" => $this->valor_total,
            "valor_pago" => 0,
            "status" => "aberto",
            "data_vencimento" => now()->addDays(30)->toDateString(),
        ]);
        $this->update(["status" => "faturada", "conta_pagar_id" => $contaPagar->id]);
    }
}
