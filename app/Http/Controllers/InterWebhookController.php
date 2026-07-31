<?php
namespace App\Http\Controllers;

use App\Models\ContaReceber;
use App\Services\InterBoletoService;
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
}
