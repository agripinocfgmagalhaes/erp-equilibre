<?php
namespace App\Services;

use App\Models\ConfiguracaoInter;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class InterBoletoService
{
    private string $baseUrl;
    private ConfiguracaoInter $config;

    public function __construct()
    {
        $this->config = ConfiguracaoInter::atual();
        if (!$this->config->configurado()) {
            throw new Exception('Integracao Inter nao configurada. Acesse Configuracoes > Integracao Inter.');
        }
        $this->baseUrl = $this->config->ambiente === 'producao'
            ? 'https://cdpj.partners.bancointer.com.br'
            : 'https://cdpj-sandbox.partners.uatinter.co';
    }

    private function http()
    {
        return Http::withOptions([
            'cert' => $this->config->certFullPath(),
            'ssl_key' => $this->config->keyFullPath(),
        ])->withHeaders(array_filter([
            'x-conta-corrente' => $this->config->conta_corrente,
        ]));
    }

    public function token(): string
    {
        return Cache::remember('inter_token', 55 * 60, function () {
            $res = $this->http()->asForm()->post("{$this->baseUrl}/oauth/v2/token", [
                'client_id' => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'grant_type' => 'client_credentials',
                'scope' => 'boleto-cobranca.write boleto-cobranca.read',
            ]);
            if ($res->failed()) throw new Exception('Falha ao autenticar no Inter: ' . $res->body());
            return $res->json('access_token');
        });
    }

    public function emitir(ContaReceber $conta): array
    {
        if ($conta->inter_codigo_solicitacao) {
            return $this->consultar($conta);
        }

        $cliente = $conta->cliente;
        $payload = [
            'seuNumero' => (string) $conta->id,
            'valorNominal' => (float) $conta->valor,
            'dataVencimento' => $conta->data_vencimento->format('Y-m-d'),
            'numDiasAgenda' => 60,
            'pagador' => [
                'cpfCnpj' => preg_replace('/\D/', '', $cliente->cpf),
                'tipoPessoa' => strlen(preg_replace('/\D/', '', $cliente->cpf)) > 11 ? 'JURIDICA' : 'FISICA',
                'nome' => $cliente->nome,
                'endereco' => $cliente->logradouro ?? 'Nao informado',
                'numero' => $cliente->numero ?? 'SN',
                'bairro' => $cliente->bairro ?? 'Nao informado',
                'cidade' => $cliente->cidade ?? 'Caucaia',
                'uf' => $cliente->estado ?? 'CE',
                'cep' => preg_replace('/\D/', '', $cliente->cep ?? '61600000'),
            ],
            'mensagem' => ['linha1' => $conta->descricao],
        ];

        $res = $this->http()->withToken($this->token())
            ->post("{$this->baseUrl}/cobranca/v3/cobrancas", $payload);

        if ($res->failed()) {
            if (preg_match('/c.digo de solicita..o:\s*([a-f0-9-]{36})/i', $res->body(), $m)) {
                $conta->update([
                    'inter_codigo_solicitacao' => $m[1],
                    'inter_situacao' => 'A_RECEBER',
                    'inter_emitido_em' => $conta->inter_emitido_em ?? now(),
                ]);
                return $this->consultar($conta);
            }
            throw new Exception('Falha ao emitir boleto: ' . $res->body());
        }

        $data = $res->json();
        $conta->update([
            'inter_codigo_solicitacao' => $data['codigoSolicitacao'],
            'inter_situacao' => 'A_RECEBER',
            'inter_emitido_em' => now(),
        ]);

        return $this->consultar($conta);
    }

    public function consultar(ContaReceber $conta): array
    {
        $res = $this->http()->withToken($this->token())
            ->get("{$this->baseUrl}/cobranca/v3/cobrancas/{$conta->inter_codigo_solicitacao}");

        if ($res->failed()) throw new Exception('Falha ao consultar boleto: ' . $res->body());

        $data = $res->json();
        $conta->update([
            'inter_situacao' => $data['cobranca']['situacao'] ?? $conta->inter_situacao,
            'inter_nosso_numero' => $data['boleto']['nossoNumero'] ?? $conta->inter_nosso_numero,
            'inter_linha_digitavel' => $data['boleto']['linhaDigitavel'] ?? $conta->inter_linha_digitavel,
            'inter_pix_copia_cola' => $data['pix']['pixCopiaECola'] ?? $conta->inter_pix_copia_cola,
        ]);

        if (($data['cobranca']['situacao'] ?? null) === 'RECEBIDO') {
            $conta->darBaixa($conta->valor, now()->toDateString());
        }

        return $data;
    }

    public function pdfBase64(ContaReceber $conta): string
    {
        $res = $this->http()->withToken($this->token())
            ->get("{$this->baseUrl}/cobranca/v3/cobrancas/{$conta->inter_codigo_solicitacao}/pdf");
        if ($res->failed()) throw new Exception('Falha ao baixar PDF: ' . $res->body());
        return $res->json('pdf');
    }

    public function cancelar(ContaReceber $conta, string $motivo = 'ACERTOS'): void
    {
        $this->http()->withToken($this->token())
            ->post("{$this->baseUrl}/cobranca/v3/cobrancas/{$conta->inter_codigo_solicitacao}/cancelar", [
                'motivoCancelamento' => $motivo,
            ]);
        $conta->update(['inter_situacao' => 'CANCELADO']);
    }

    public function registrarWebhook(string $url): array
    {
        $res = $this->http()->withToken($this->token())
            ->put("{$this->baseUrl}/cobranca/v3/cobrancas/webhook", [
                'webhookUrl' => $url,
            ]);
        if ($res->failed()) throw new Exception('Falha ao registrar webhook: ' . $res->body());
        return $res->json() ?? ['status' => 'registrado'];
    }
}
