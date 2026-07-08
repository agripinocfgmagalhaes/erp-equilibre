<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ContaPagar extends Model
{
    protected $table = 'contas_pagar';
    protected $fillable = ['descricao','contato_tipo','contato_id','plano_conta_id','conta_bancaria_id','projeto_id','fase_obra_id','pedido_compra_id','valor','valor_pago','data_vencimento','data_pagamento','status','observacoes'];
    protected $casts = ['valor' => 'decimal:2', 'valor_pago' => 'decimal:2', 'data_vencimento' => 'date', 'data_pagamento' => 'date'];
    public function planoConta(): BelongsTo { return $this->belongsTo(PlanoConta::class); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function faseObra(): BelongsTo { return $this->belongsTo(FaseObra::class); }
    public function pedidoCompra(): BelongsTo { return $this->belongsTo(PedidoCompra::class); }
    public function getNomeContatoAttribute(): string
    {
        if (! $this->contato_tipo || ! $this->contato_id) return '—';
        $model = match ($this->contato_tipo) {
            'cliente' => Cliente::find($this->contato_id),
            'corretor' => Corretor::find($this->contato_id),
            'fornecedor' => Fornecedor::find($this->contato_id),
            'prestador' => Prestador::find($this->contato_id),
            default => null,
        };
        return $model ? ucfirst($this->contato_tipo).' - '.$model->nome : '—';
    }
    public function darBaixa(float $valorPago, ?string $dataPagamento = null, ?int $contaBancariaId = null): void
    {
        $contaBancariaId = $contaBancariaId ?? $this->conta_bancaria_id;
        $dataPagamento = $dataPagamento ?? now()->toDateString();
        $this->update(['valor_pago' => $valorPago, 'data_pagamento' => $dataPagamento, 'conta_bancaria_id' => $contaBancariaId, 'status' => $valorPago >= $this->valor ? 'pago' : 'aberto']);
        if ($contaBancariaId) LancamentoBancario::registrarBaixa('conta_pagar', $this->id, $contaBancariaId, 'saida', $this->descricao, $valorPago, $dataPagamento);
    }
    protected static function booted(): void
    {
        static::saving(function (ContaPagar $conta) {
            if ($conta->status === 'aberto' && $conta->data_vencimento && $conta->data_vencimento->isPast()) $conta->status = 'vencido';
        });
    }
}
