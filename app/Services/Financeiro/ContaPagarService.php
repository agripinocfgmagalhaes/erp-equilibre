<?php

namespace App\Services\Financeiro;

use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\LancamentoBancario;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContaPagarService
{
    public function darBaixa(ContaPagar $conta, float $valorPago, ?string $dataPagamento = null, ?int $contaBancariaId = null): ContaPagar
    {
        if (in_array($conta->status, ['pago', 'cancelado'], true)) {
            throw new InvalidArgumentException("Título já está {$conta->status}.");
        }

        if ($valorPago <= 0) {
            throw new InvalidArgumentException('Valor pago deve ser maior que zero.');
        }

        if ($valorPago > (float) $conta->valor) {
            throw new InvalidArgumentException('Valor pago não pode exceder o valor do título.');
        }

        $contaBancariaId = $contaBancariaId ?? $conta->conta_bancaria_id;

        if ($contaBancariaId && ! ContaBancaria::whereKey($contaBancariaId)->where('ativo', true)->exists()) {
            throw new InvalidArgumentException('Conta bancária inválida ou inativa.');
        }

        $dataPagamento = $dataPagamento ?? now()->toDateString();

        DB::transaction(function () use ($conta, $valorPago, $dataPagamento, $contaBancariaId) {
            $conta->update([
                'valor_pago' => $valorPago,
                'data_pagamento' => $dataPagamento,
                'conta_bancaria_id' => $contaBancariaId,
                'status' => $valorPago >= (float) $conta->valor ? 'pago' : 'aberto',
            ]);

            if ($contaBancariaId) {
                LancamentoBancario::registrarBaixa('conta_pagar', $conta->id, $contaBancariaId, 'saida', $conta->descricao, $valorPago, $dataPagamento);
            }

            if ($valorPago >= (float) $conta->valor && $conta->medicao) {
                $conta->medicao->update(['status' => 'paga']);
            }
        });

        return $conta->fresh();
    }
}
