<?php

namespace App\Services\Financeiro;

use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use App\Models\LancamentoBancario;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContaReceberService
{
    public function darBaixa(ContaReceber $conta, float $valorRecebido, ?string $dataRecebimento = null, ?int $contaBancariaId = null): ContaReceber
    {
        if (in_array($conta->status, ['recebido', 'cancelado'], true)) {
            throw new InvalidArgumentException("Título já está {$conta->status}.");
        }

        if ($valorRecebido <= 0) {
            throw new InvalidArgumentException('Valor recebido deve ser maior que zero.');
        }

        if ($valorRecebido > (float) $conta->valor) {
            throw new InvalidArgumentException('Valor recebido não pode exceder o valor do título.');
        }

        $contaBancariaId = $contaBancariaId ?? $conta->conta_bancaria_id;

        if ($contaBancariaId && ! ContaBancaria::whereKey($contaBancariaId)->where('ativo', true)->exists()) {
            throw new InvalidArgumentException('Conta bancária inválida ou inativa.');
        }

        $dataRecebimento = $dataRecebimento ?? now()->toDateString();

        DB::transaction(function () use ($conta, $valorRecebido, $dataRecebimento, $contaBancariaId) {
            $conta->update([
                'valor_recebido' => $valorRecebido,
                'data_recebimento' => $dataRecebimento,
                'conta_bancaria_id' => $contaBancariaId,
                'status' => $valorRecebido >= (float) $conta->valor ? 'recebido' : 'aberto',
            ]);

            if ($contaBancariaId) {
                LancamentoBancario::registrarBaixa('conta_receber', $conta->id, $contaBancariaId, 'entrada', $conta->descricao, $valorRecebido, $dataRecebimento);
            }
        });

        return $conta->fresh();
    }
}
