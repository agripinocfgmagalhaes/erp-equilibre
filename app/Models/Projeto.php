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
    public function orcamentoItens(): HasMany { return $this->hasMany(OrcamentoItem::class); }
    public function ordensServico(): HasMany { return $this->hasMany(OrdenServico::class); }

    public function avancoFisico(): float
    {
        $itens = $this->orcamentoItens()->with('ordemServicoItens.medicaoItens')->get();

        $temMedicao = $itens->contains(
            fn (OrcamentoItem $item) => $item->ordemServicoItens->contains(
                fn (OrdenServicoItem $osi) => $osi->medicaoItens->isNotEmpty()
            )
        );

        if ($temMedicao) {
            $orcadoTotal = (float) $itens->sum('valor_total');
            if ($orcadoTotal > 0) {
                $realizado = $itens->sum(function (OrcamentoItem $item) {
                    $qtdMedida = $item->ordemServicoItens->sum(
                        fn (OrdenServicoItem $osi) => (float) ($osi->medicaoItens->max('quantidade_acumulada') ?? 0)
                    );
                    $qtdMedida = min($qtdMedida, (float) $item->quantidade);
                    return $qtdMedida * (float) $item->valor_unitario;
                });
                return round(($realizado / $orcadoTotal) * 100, 2);
            }
        }

        // fallback: orçamento existe (até importado via CSV), mas ainda sem medição de execução -> percentual manual por fase
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
