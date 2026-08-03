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
}
