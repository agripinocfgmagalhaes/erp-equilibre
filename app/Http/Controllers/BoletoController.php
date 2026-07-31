<?php
namespace App\Http\Controllers;

use App\Models\ContaReceber;
use App\Services\InterBoletoService;
use Illuminate\Http\Response;

class BoletoController extends Controller
{
    public function pdf(ContaReceber $contaReceber): Response
    {
        $service = app(InterBoletoService::class);
        $pdfBase64 = $service->pdfBase64($contaReceber);

        return response(base64_decode($pdfBase64), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="boleto-' . $contaReceber->id . '.pdf"',
        ]);
    }

    public function pdfPublico(string $codigoSolicitacao): Response
    {
        $conta = ContaReceber::where('inter_codigo_solicitacao', $codigoSolicitacao)->firstOrFail();
        $service = app(InterBoletoService::class);
        $pdfBase64 = $service->pdfBase64($conta);

        return response(base64_decode($pdfBase64), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="boleto-' . $conta->id . '.pdf"',
        ]);
    }
}
