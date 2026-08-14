<?php
namespace App\Services;

use App\Models\ConfiguracaoInter;
use App\Models\ContaPagar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class InterPixPagamentoService
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
        ])->timeout(60)->connectTimeout(10)->withHeaders(array_filter([
            'x-conta-corrente' => $this->config->conta_corrente,
        ]));
    }

    public function token(): string
    {
        return Cache::remember('inter_token_pix_pagamento', 55 * 60, function () {
            $res = $this->http()->asForm()->post("{$this->baseUrl}/oauth/v2/token", [
                'client_id' => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'grant_type' => 'client_credentials',
                'scope' => 'pagamento-pix.write pagamento-pix.read',
            ]);
            if ($res->failed()) throw new Exception('Falha ao autenticar no Inter: ' . $res->body());
            return $res->json('access_token');
        });
    }

    /**
     * Resolve chave/tipo de Pix a partir do contato vinculado a ContaPagar.
     * Suporta Funcionario e Prestador (ambos com chave_pix/tipo_chave_pix).
     */
    public function resolverChaveContato(ContaPagar $conta): array
    {
        $model = match ($conta->contato_tipo) {
            'funcionario' => \App\Models\Funcionario::find($conta->contato_id),
            'prestador' => \App\Models\Prestador::find($conta->contato_id),
            default => null,
        };

        if ($model && $model->chave_pix) {
            return ['chave' => $model->chave_pix, 'tipo' => $model->tipo_chave_pix];
        }

        return ['chave' => null, 'tipo' => null];
    }

    /**
     * Envia um Pix Pagamento referente a uma ContaPagar.
     * Endpoint /banking/v2/pix validado em producao em 14/08/2026.
     */
    public function enviar(ContaPagar $conta, string $chavePix, string $tipoChave, ?float $valor = null): array
    {
        $valor = $valor ?? (float) $conta->valor;

        $payload = [
            'valor' => number_format($valor, 2, '.', ''),
            'destinatario' => [
                'tipo' => 'CHAVE',
                'chave' => $chavePix,
            ],
            'descricao' => $conta->descricao,
        ];

        $res = $this->http()->withToken($this->token())
            ->post("{$this->baseUrl}/banking/v2/pix", $payload);

        if ($res->failed()) {
            throw new Exception('Falha ao enviar Pix: ' . $res->body());
        }

        $data = $res->json();

        $conta->update([
            'chave_pix_destino' => $chavePix,
            'tipo_chave_pix_destino' => $tipoChave,
            'inter_pix_e2e_id' => $data['endToEndId'] ?? $data['codigoSolicitacao'] ?? null,
            'inter_pix_status' => $data['status'] ?? 'ENVIADO',
            'inter_pix_enviado_em' => now(),
        ]);

        if (($data['status'] ?? null) === 'REALIZADO') {
            $conta->darBaixa($valor, now()->toDateString());
        }

        return $data;
    }

    /**
     * Reconsulta o status oficial de um Pix Pagamento ja enviado e atualiza a ContaPagar.
     * Chamado pelo webhook (que so avisa que algo mudou, nao traz o status definitivo confiavel)
     * e pode tambem ser chamado manualmente/via schedule como fallback.
     */
    public function consultar(ContaPagar $conta): array
    {
        if (!$conta->inter_pix_e2e_id) {
            throw new Exception('ContaPagar sem inter_pix_e2e_id - nunca foi enviada via Pix.');
        }

        $res = $this->http()->withToken($this->token())
            ->get("{$this->baseUrl}/banking/v2/pix/{$conta->inter_pix_e2e_id}");

        if ($res->failed()) {
            throw new Exception('Falha ao consultar Pix: ' . $res->body());
        }

        $data = $res->json();
        $status = $data['status'] ?? $conta->inter_pix_status;

        $conta->update(['inter_pix_status' => $status]);

        if ($status === 'REALIZADO' && $conta->status !== 'pago') {
            $conta->darBaixa((float) $conta->valor, now()->toDateString());
        }

        return $data;
    }
}
