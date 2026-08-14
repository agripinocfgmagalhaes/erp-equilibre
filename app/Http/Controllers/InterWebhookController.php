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
        $e2eId = $payload['endToEndId'] ?? $payload['e2eId'] ?? null;

        if ($codigoSolicitacao) {
            $conta = ContaReceber::where('inter_codigo_solicitacao', $codigoSolicitacao)->first();
            if ($conta) {
                try {
                    app(InterBoletoService::class)->consultar($conta);
                    return response()->json(['status' => 'ok - cobranca']);
                } catch (\Throwable $e) {
                    Log::error('Erro ao processar webhook cobranca: ' . $e->getMessage());
                    return response()->json(['status' => 'erro'], 200);
                }
            }
        }

        $idPix = $e2eId ?? $codigoSolicitacao;
        if ($idPix) {
            $conta = ContaPagar::where('inter_pix_e2e_id', $idPix)->first();
            if ($conta) {
                try {
                    app(InterPixPagamentoService::class)->consultar($conta);
                    return response()->json(['status' => 'ok - pix pagamento']);
                } catch (\Throwable $e) {
                    Log::error('Erro ao processar webhook pix pagamento: ' . $e->getMessage());
                    return response()->json(['status' => 'erro'], 200);
                }
            }
        }

        return response()->json(['status' => 'ignorado - conta nao encontrada']);
    }
}
