<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Projeto extends Model
{
    protected $table = 'projetos';
    protected $fillable = ['nome','descricao','status','cor','data_inicio','data_previsao_fim','valor_orcamento'];
    protected $casts = ['valor_orcamento' => 'decimal:2'];
    
    public function unidades(): HasMany { return $this->hasMany(Unidade::class); }
    public function fasesObra(): HasMany { return $this->hasMany(FaseObra::class); }
    public function orcamentoItens(): HasMany { return $this->hasMany(OrcamentoItem::class); }
    public function ordensServico(): HasMany { return $this->hasMany(OrdenServico::class); }
    public function contasPagar(): HasMany { return $this->hasMany(ContaPagar::class); }

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

    public function painelCustoPorFase(): array
    {
        $fases = $this->fasesObra()->orderBy("ordem")->get();
        $linhas = [];
        $totalOrcado = 0; $totalRealizado = 0; $somaPercXPeso = 0; $somaPeso = 0;

        foreach ($fases as $fase) {
            $orcado = (float) DB::table("orcamento_itens")
                ->where("projeto_id", $this->id)
                ->where("fase_padrao_id", $fase->fase_padrao_id)
                ->sum("valor_total");

            $realizado = (float) DB::table("itens_pedido_compra as i")
                ->join("pedidos_compra as p", "p.id", "=", "i.pedido_compra_id")
                ->where("p.projeto_id", $this->id)
                ->where("p.fase_obra_id", $fase->id)
                ->whereNotIn("p.status", ["cancelado"])
                ->sum("i.valor_total");

            $perc = (float) $fase->percentual;
            $peso = (float) ($fase->peso ?: 1);

            $somaPercXPeso += $perc * $peso;
            $somaPeso += $peso;
            $totalOrcado += $orcado;
            $totalRealizado += $realizado;

            $linhas[] = [
                "nome"         => $fase->nome,
                "ordem"        => (int) $fase->ordem,
                "orcado"       => $orcado,
                "realizado"    => $realizado,
                "desvio"       => $realizado - $orcado,
                "desvio_pct"   => $orcado > 0 ? round(($realizado - $orcado) / $orcado * 100, 1) : null,
                "perc"         => $perc,
                "peso"         => $peso,
                "fase_padrao_id" => $fase->fase_padrao_id,
                "fase_obra_id"   => $fase->id,
                "orcado_mat"     => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('fase_padrao_id', $fase->fase_padrao_id)->where('tipo', 'material')->sum('valor_total'),
                "orcado_mdo"     => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('fase_padrao_id', $fase->fase_padrao_id)->where('tipo', 'mdo')->sum('valor_total'),
                "orcado_outros"  => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('fase_padrao_id', $fase->fase_padrao_id)->where('tipo', 'outros')->sum('valor_total'),
            ];
        }

        $avancoFisico = $somaPeso > 0 ? round($somaPercXPeso / $somaPeso, 2) : 0;

        return [
            "linhas"            => $linhas,
            "total_orcado"      => $totalOrcado,
            "total_mat"       => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('tipo', 'material')->sum('valor_total'),
            "total_mdo"       => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('tipo', 'mdo')->sum('valor_total'),
            "total_outros"    => (float) DB::table('orcamento_itens')->where('projeto_id', $this->id)->where('tipo', 'outros')->sum('valor_total'),
            "total_realizado"   => $totalRealizado,
            "total_desvio"      => $totalRealizado - $totalOrcado,
            "avanco_fisico"     => $avancoFisico,
        ];
    }

    public function gerarFasesPadrao(): int
    {
        if (DB::table('fases_obra')->where('projeto_id', $this->id)->exists()) return 0;
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('fases_obra');
        $n = 0;
        foreach (FasePadrao::orderBy('id')->get() as $i => $fp) {
            $row = ['projeto_id' => $this->id, 'created_at' => now(), 'updated_at' => now()];
            foreach (['nome', 'peso', 'ordem'] as $col) if (in_array($col, $cols)) $row[$col] = $fp->{$col} ?? ($col === 'ordem' ? $i + 1 : ($col === 'peso' ? 0 : null));
            if (in_array('fase_padrao_id', $cols)) $row['fase_padrao_id'] = $fp->id;
            if (in_array('percentual', $cols)) $row['percentual'] = 0;
            DB::table('fases_obra')->insert($row);
            $n++;
        }
        return $n;
    }

    protected static function booted(): void
    {
        static::created(fn (Projeto $p) => $p->gerarFasesPadrao());
    }
}
