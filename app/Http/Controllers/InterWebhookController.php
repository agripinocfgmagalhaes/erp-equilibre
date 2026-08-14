<?php
namespace App\Http\Controllers;

use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Services\InterBoletoService;
use App\Services\InterPixPagamentoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InterWebhookController extends Controller
{
    public function receber(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Webhook Inter recebido', $payload);

        $codigoSolicitacao = $payload['codigoSolicitacao'] ?? null;
        if (!$codigoSolicitacao) {
            return response()->json(['status' => 'ignorado']);
        }

        $conta = ContaReceber::where('inter_codigo_solicitacao', $codigoSolicitacao)->first();
        if (!$conta) {
            return response()->json(['status' => 'conta nao encontrada']);
        }

        try {
            app(InterBoletoService::class)->consultar($conta);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook Inter: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }

    public function receberPix(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Webhook Inter Pix Pagamento recebido', $payload);

        $e2eId = $payload['endToEndId'] ?? $payload['codigoSolicitacao'] ?? $payload['e2eId'] ?? null;
        if (!$e2eId) {
            return response()->json(['status' => 'ignorado - sem identificador']);
        }

        $conta = ContaPagar::where('inter_pix_e2e_id', $e2eId)->first();
        if (!$conta) {
            return response()->json(['status' => 'conta nao encontrada']);
        }

        try {
            app(InterPixPagamentoService::class)->consultar($conta);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook Pix Pagamento: ' . $e->getMessage());
        }

        return response()->json(['status' => 'ok']);
    }
}
