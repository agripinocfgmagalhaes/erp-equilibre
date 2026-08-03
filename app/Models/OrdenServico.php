<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OrdenServico extends Model
{
    protected $table = 'ordens_servico';
    protected $fillable = ['numero','data','projeto_id','prestador_id','fase_obra_id','descricao','valor_total','data_inicio','data_previsao_fim','data_conclusao','status'];
    protected $casts = ['valor_total' => 'decimal:2', 'data' => 'date', 'data_inicio' => 'date', 'data_previsao_fim' => 'date', 'data_conclusao' => 'date'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function prestador(): BelongsTo { return $this->belongsTo(Prestador::class); }
    public function faseObra(): BelongsTo { return $this->belongsTo(FaseObra::class, 'fase_obra_id'); }
    public function medicoes(): HasMany { return $this->hasMany(Medicao::class); }
    public function contasPagar(): HasMany { return $this->hasMany(ContaPagar::class, 'ordem_servico_id'); }
    public function valorMedido(): float
    {
        return (float) $this->medicoes()->whereIn('status', ['aprovada', 'faturada', 'paga'])->sum('valor_total');
    }
    public function percentualMedido(): float
    {
        if ((float) $this->valor_total <= 0) return 0.0;
        return round(($this->valorMedido() / (float) $this->valor_total) * 100, 2);
    }
}
