<?php
namespace App\Observers;
use App\Models\ContaReceber;
use App\Models\LancamentoBancario;
class ContaReceberObserver
{
    public function updated(ContaReceber $conta): void
    {
        if ($conta->status !== 'recebido' || ! $conta->conta_bancaria_id || ! $conta->data_recebimento) return;
        LancamentoBancario::registrarBaixa('conta_receber', $conta->id, $conta->conta_bancaria_id, 'entrada', $conta->descricao, (float) $conta->valor_recebido, $conta->data_recebimento->toDateString());
    }
    public function deleted(ContaReceber $conta): void
    {
        LancamentoBancario::where('origem', 'conta_receber')->where('origem_id', $conta->id)->delete();
    }
}
