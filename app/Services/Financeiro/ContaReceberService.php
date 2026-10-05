<?php

namespace App\Services\Financeiro;

use App\Models\ContaBancaria;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContaReceberService
{
    /**
     * @param float $valorPago Valor DESTE pagamento (não o acumulado). Somado ao que já
     *                         havia sido recebido antes, para suportar baixa parcial em varias vezes.
     */
    public function darBaixa(ContaReceber $conta, float $valorPago, ?string $dataRecebimento = null, ?int $contaBancariaId = null, bool $permitirExcedente = false): ContaReceber
    {
        if (in_array($conta->status, ['recebido', 'cancelado'], true)) {
            throw new InvalidArgumentException("Título já está {$conta->status}.");
        }

        if ($valorPago <= 0) {
            throw new InvalidArgumentException('Valor recebido deve ser maior que zero.');
        }

        $totalRecebido = round((float) $conta->valor_recebido + $valorPago, 2);

        if (! $permitirExcedente && $totalRecebido > (float) $conta->valor + 0.01) {
            $restante = round((float) $conta->valor - (float) $conta->valor_recebido, 2);
            throw new InvalidArgumentException("Valor excede o saldo em aberto do título (restam R$ " . number_format($restante, 2, ',', '.') . ").");
        }

        $contaBancariaId = $contaBancariaId ?? $conta->conta_bancaria_id;

        if ($contaBancariaId && ! ContaBancaria::whereKey($contaBancariaId)->where('ativo', true)->exists()) {
            throw new InvalidArgumentException('Conta bancária inválida ou inativa.');
        }

        $dataRecebimento = $dataRecebimento ?? now()->toDateString();

        DB::transaction(function () use ($conta, $totalRecebido, $dataRecebimento, $contaBancariaId) {
            $conta->update([
                'valor_recebido' => $totalRecebido,
                'data_recebimento' => $dataRecebimento,
                'conta_bancaria_id' => $contaBancariaId,
                'status' => $totalRecebido >= (float) $conta->valor ? 'recebido' : 'parcial',
            ]);
        });

        return $conta->fresh();
    }
}
