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
    public function itens() { return $this->hasMany(MedicaoItem::class); }

    public function aprovarEGerarContaPagar(): void
    {
        $os = $this->ordemServico;
        $percentualAcumulado = $os->valor_total > 0
            ? round((($os->valorMedido() + (float) $this->valor_total) / (float) $os->valor_total) * 100, 2)
            : 0;
        $this->update(["status" => "aprovada", "data_aprovacao" => now(), "percentual_acumulado" => $percentualAcumulado]);
        $contaPagar = ContaPagar::create([
            "descricao" => "Medição ".$this->numero." - OS ".$os->numero,
            "contato_tipo" => $os->prestador_id ? "prestador" : null,
            "contato_id" => $os->prestador_id,
            "projeto_id" => $os->projeto_id,
            "fase_obra_id" => $os->fase_obra_id,
            "ordem_servico_id" => $os->id,
            "valor" => $this->valor_total,
            "valor_pago" => 0,
            "status" => "aberto",
            "data_vencimento" => now()->addDays(30)->toDateString(),
        ]);
        $this->update(["status" => "faturada", "conta_pagar_id" => $contaPagar->id]);
    }

    protected static function booted(): void
    {
        static::creating(function (Medicao $medicao) {
            if (! $medicao->usuario_medicao_id && auth()->check()) {
                $medicao->usuario_medicao_id = auth()->id();
            }
        });
    }
}
