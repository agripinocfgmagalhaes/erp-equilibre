<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use App\Services\InterBoletoService;
class ContratoVenda extends Model
{
    protected $table = 'contratos_venda';
    protected $fillable = ['numero','unidade_id','cliente_id','corretor_id','status','valor_venda','valor_entrada','valor_sinal','valor_parcelamento','valor_fgts','valor_financiamento','valor_subsidio','qtd_parcelas','taxa_juros','valor_parcela','percentual_comissao','valor_comissao','data_contrato','data_entrega_prevista','observacoes'];
    protected $casts = ['valor_venda' => 'decimal:2', 'valor_entrada' => 'decimal:2', 'valor_sinal' => 'decimal:2', 'valor_parcelamento' => 'decimal:2', 'valor_fgts' => 'decimal:2', 'valor_financiamento' => 'decimal:2', 'valor_subsidio' => 'decimal:2', 'taxa_juros' => 'decimal:3', 'valor_parcela' => 'decimal:2', 'percentual_comissao' => 'decimal:2', 'valor_comissao' => 'decimal:2', 'data_contrato' => 'date', 'data_entrega_prevista' => 'date'];
    public function unidade(): BelongsTo { return $this->belongsTo(Unidade::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function corretor(): BelongsTo { return $this->belongsTo(Corretor::class); }
    public function contasReceber(): HasMany { return $this->hasMany(ContaReceber::class); }
    public function getValorRepasseAttribute(): float { return (float) $this->valor_fgts + (float) $this->valor_financiamento + (float) $this->valor_subsidio; }
    public static function gerarNumero(?string $identificacaoUnidade = null): string
    {
        $ano = now()->year;
        $apto = $identificacaoUnidade ? preg_replace('/[^A-Za-z0-9]/', '', $identificacaoUnidade) : 'SN';
        return sprintf('CV-%s-%d-%04d', $apto, $ano, Sequencia::proximo('CV', $ano));
    }
    protected static function booted(): void
    {
        static::creating(function (ContratoVenda $c) {
            if (empty($c->numero)) {
                $identificacao = $c->unidade_id ? Unidade::find($c->unidade_id)?->identificacao : null;
                $c->numero = static::gerarNumero($identificacao);
            }
            $c->percentual_comissao = 4.5; $c->valor_comissao = round((float) $c->valor_venda * 4.5 / 100, 2);
        });
        static::updating(function (ContratoVenda $c) { if ($c->isDirty('valor_venda')) { $c->percentual_comissao = 4.5; $c->valor_comissao = round((float) $c->valor_venda * 4.5 / 100, 2); } });
        static::created(function (ContratoVenda $c) { $c->unidade->update(['status' => 'vendido']); });
        static::updated(function (ContratoVenda $c) { if (in_array($c->status, ['distratado','cancelado'])) { $c->unidade->update(['status' => 'disponivel']); $c->cancelarTitulosAbertos(); } });
    }

    public function baloes(): HasMany
    {
        return $this->hasMany(Balao::class)->orderBy('ordem');
    }

    public function cancelarTitulosAbertos(): int
    {
        $cancelados = 0;

        foreach ($this->contasReceber()->whereIn('status', ['aberto', 'vencido'])->get() as $conta) {
            if ($conta->inter_codigo_solicitacao) {
                try {
                    app(InterBoletoService::class)->cancelar($conta);
                } catch (\Throwable $e) {
                    Log::warning("Falha ao cancelar boleto no Inter (CR {$conta->id}): {$e->getMessage()}");
                }
            }
            $conta->update(['status' => 'cancelado']);
            $cancelados++;
        }

        return $cancelados;
    }
}
