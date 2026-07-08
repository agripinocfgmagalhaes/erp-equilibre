<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    public static function gerarNumero(): string
    {
        $ano = now()->year;
        $ultimo = static::where('numero', 'like', "CV-{$ano}-%")->count();
        return sprintf('CV-%d-%04d', $ano, $ultimo + 1);
    }
    protected static function booted(): void
    {
        static::creating(function (ContratoVenda $c) { $c->percentual_comissao = 4.5; $c->valor_comissao = round((float) $c->valor_venda * 4.5 / 100, 2); });
        static::updating(function (ContratoVenda $c) { if ($c->isDirty('valor_venda')) { $c->percentual_comissao = 4.5; $c->valor_comissao = round((float) $c->valor_venda * 4.5 / 100, 2); } });
        static::created(function (ContratoVenda $c) { $c->unidade->update(['status' => 'vendido']); });
        static::updated(function (ContratoVenda $c) { if (in_array($c->status, ['distratado','cancelado'])) $c->unidade->update(['status' => 'distratado']); });
    }
}
