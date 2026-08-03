<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Projeto extends Model
{
    protected $table = 'projetos';
    protected $fillable = ['nome','descricao','status','cor','data_inicio','data_previsao_fim','valor_orcamento'];
    protected $casts = ['valor_orcamento' => 'decimal:2'];
    public function unidades(): HasMany { return $this->hasMany(Unidade::class); }
    public function fasesObra(): HasMany { return $this->hasMany(FaseObra::class); }
    public function ordensServico(): HasMany { return $this->hasMany(OrdenServico::class); }

    public function avancoFisico(): float
    {
        $fases = $this->fasesObra;
        if ($fases->isEmpty()) return 0.0;
        $pesoTotal = (float) $fases->sum('peso');
        if ($pesoTotal <= 0) return round((float) $fases->avg('percentual'), 2);
        $soma = $fases->sum(fn ($f) => (float) $f->percentual * (float) $f->peso);
        return round($soma / $pesoTotal, 2);
    }

    public function avancoFinanceiro(): float
    {
        if (! $this->valor_orcamento || (float) $this->valor_orcamento <= 0) return 0.0;
        $pago = (float) $this->contasPagar()->where('status', 'pago')->sum('valor_pago');
        return round(($pago / (float) $this->valor_orcamento) * 100, 2);
    }

    public function contasPagar(): HasMany { return $this->hasMany(ContaPagar::class); }
}
