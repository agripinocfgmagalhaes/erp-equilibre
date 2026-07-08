<?php
namespace App\Observers;
use App\Models\ContaPagar;
use App\Models\LancamentoBancario;
class ContaPagarObserver
{
    public function updated(ContaPagar $conta): void
    {
        if ($conta->status !== 'pago' || ! $conta->conta_bancaria_id || ! $conta->data_pagamento) return;
        LancamentoBancario::registrarBaixa('conta_pagar', $conta->id, $conta->conta_bancaria_id, 'saida', $conta->descricao, (float) $conta->valor_pago, $conta->data_pagamento->toDateString());
    }
    public function deleted(ContaPagar $conta): void
    {
        LancamentoBancario::where('origem', 'conta_pagar')->where('origem_id', $conta->id)->delete();
    }
}
