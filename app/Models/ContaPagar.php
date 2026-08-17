<?php

namespace App\Models;

use App\Services\Financeiro\ContaPagarService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaPagar extends Model
{
    protected $table = 'contas_pagar';

    protected $fillable = ['descricao','numero_documento','contato_tipo','contato_id','plano_conta_id','conta_bancaria_id','projeto_id','fase_obra_id','fase_padrao_id','pedido_compra_id','ordem_servico_id','valor','valor_pago','data_vencimento','data_pagamento','status','observacoes','chave_pix_destino','tipo_chave_pix_destino','inter_pix_e2e_id','inter_pix_status','inter_pix_enviado_em'];

    protected $casts = ['valor' => 'decimal:2', 'valor_pago' => 'decimal:2', 'data_vencimento' => 'date', 'data_pagamento' => 'date'];

    public function planoConta(): BelongsTo { return $this->belongsTo(PlanoConta::class); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function faseObra(): BelongsTo { return $this->belongsTo(FaseObra::class); }
    public function fasePadrao(): BelongsTo { return $this->belongsTo(FasePadrao::class, 'fase_padrao_id'); }
    public function pedidoCompra(): BelongsTo { return $this->belongsTo(PedidoCompra::class); }
    public function ordemServico(): BelongsTo { return $this->belongsTo(OrdenServico::class, 'ordem_servico_id'); }
    public function medicao() { return $this->hasOne(Medicao::class, 'conta_pagar_id'); }

    public function getNomeContatoAttribute(): string
    {
        if (! $this->contato_tipo || ! $this->contato_id) return '—';

        $model = match ($this->contato_tipo) {
            'cliente' => Cliente::find($this->contato_id),
            'corretor' => Corretor::find($this->contato_id),
            'fornecedor' => Fornecedor::find($this->contato_id),
            'prestador' => Prestador::find($this->contato_id),
            'funcionario' => Funcionario::find($this->contato_id),
            default => null,
        };

        return $model ? ucfirst($this->contato_tipo).' - '.$model->nome : '—';
    }

    public function darBaixa(float $valorPago, ?string $dataPagamento = null, ?int $contaBancariaId = null): void
    {
        app(ContaPagarService::class)->darBaixa($this, $valorPago, $dataPagamento, $contaBancariaId);
    }

    protected static function booted(): void
    {
        static::saving(function (ContaPagar $conta) {
            if ($conta->status === 'aberto' && $conta->data_vencimento && $conta->data_vencimento->isPast()) $conta->status = 'vencido';
        });
    }
}
