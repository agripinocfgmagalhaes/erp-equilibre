<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientePortalController extends Controller
{
    public function boletos(Request $request): View
    {
        $cpf = preg_replace('/\D/', '', (string) $request->query('cpf'));
        $contas = collect();

        if (strlen($cpf) >= 11) {
            $contas = ContaReceber::whereHas('cliente', function ($q) use ($cpf) {
                $q->whereRaw("REGEXP_REPLACE(cpf, '[^0-9]', '') = ?", [$cpf]);
            })
                ->whereIn('status', ['aberto', 'vencido'])
                ->whereNotNull('inter_codigo_solicitacao')
                ->with('cliente')
                ->orderBy('data_vencimento')
                ->get();
        }

        return view('portal.boletos', [
            'cpf' => $request->query('cpf'),
            'contas' => $contas,
            'buscou' => $request->has('cpf'),
        ]);
    }
}
