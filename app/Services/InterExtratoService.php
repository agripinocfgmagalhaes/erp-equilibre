<?php
namespace App\Services;

use App\Models\ConfiguracaoInter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class InterExtratoService
{
    private string $baseUrl;
    private ConfiguracaoInter $config;

    public function __construct()
    {
        $this->config = ConfiguracaoInter::atual();
        if (!$this->config->configurado()) {
            throw new Exception('Integracao Inter nao configurada.');
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
        ])->timeout(60)->connectTimeout(10)->withHeaders(array_filter([
            'x-conta-corrente' => $this->config->conta_corrente,
        ]));
    }

    public function token(): string
    {
        return Cache::remember('inter_token_extrato', 55 * 60, function () {
            $res = $this->http()->asForm()->post("{$this->baseUrl}/oauth/v2/token", [
                'client_id' => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'grant_type' => 'client_credentials',
                'scope' => 'extrato.read saldo.read',
            ]);
            if ($res->failed()) throw new Exception('Falha ao autenticar no Inter: ' . $res->body());
            return $res->json('access_token');
        });
    }

    /**
     * CHUTE EDUCADO - path e formato ainda nao confirmados contra a API real.
     * Padrao mais comum entre integracoes de terceiros com o Inter: dataInicio/dataFim (Y-m-d).
     */
    public function buscarExtrato(string $dataInicio, string $dataFim): array
    {
        $res = $this->http()->withToken($this->token())
            ->get("{$this->baseUrl}/banking/v2/extrato", [
                'dataInicio' => $dataInicio,
                'dataFim' => $dataFim,
            ]);

        if ($res->failed()) {
            throw new Exception('Falha ao consultar extrato: ' . $res->body());
        }

        return $res->json();
    }

    public function buscarSaldo(): array
    {
        $res = $this->http()->withToken($this->token())->get("{$this->baseUrl}/banking/v2/saldo");
        if ($res->failed()) {
            throw new Exception('Falha ao consultar saldo: ' . $res->body());
        }
        return $res->json();
    }
}
