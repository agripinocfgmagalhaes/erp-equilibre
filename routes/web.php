<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/boletos/{contaReceber}/pdf', [\App\Http\Controllers\BoletoController::class, 'pdf'])->name('boleto.pdf')->middleware(['auth', 'throttle:portal']);
Route::get('/boletos/publico/{codigoSolicitacao}/pdf', [\App\Http\Controllers\BoletoController::class, 'pdfPublico'])->name('boleto.pdf.publico')->middleware('throttle:portal');
Route::post('/webhooks/inter/cobranca', [\App\Http\Controllers\InterWebhookController::class, 'receber'])->name('webhook.inter.cobranca');
Route::get('/meus-boletos', [\App\Http\Controllers\ClientePortalController::class, 'boletos'])->name('portal.boletos')->middleware('throttle:portal');

// Download do template CSV de clientes (via PHP, evita 403 de arquivo estático)
Route::get('/download/clientes-template', function () {
    $linhas = [
        ['nome','cpf','email','telefone','whatsapp','renda_familiar','estado_civil','profissao','conjuge_nome','conjuge_cpf','conjuge_renda','conjuge_profissao','conjuge_email','conjuge_telefone','cep','logradouro','numero','complemento','bairro','cidade','estado','observacoes','ativo'],
        ['João da Silva','123.456.789-01','joao@email.com','(11)99999-8888','(11)99999-8888','5000,00','casado','Engenheiro Civil','Maria da Silva','987.654.321-00','4500,00','Professora','maria@email.com','(11)88888-7777','01310-100','Av Paulista','1000','Apto 42','Bela Vista','São Paulo','SP','Cliente MCMV','1'],
        ['Ana Souza','234.567.890-12','ana@email.com','(11)77777-6666','(11)77777-6666','3500,50','solteiro','Designer','','','','','','','04567-890','Rua das Flores','500','','Vila Nova','São Paulo','SP','','1'],
    ];

    return response()->streamDownload(function () use ($linhas) {
        $handle = fopen('php://output', 'w');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM UTF-8 para abrir correto no Excel
        foreach ($linhas as $linha) {
            fwrite($handle, implode(';', $linha)."\n");
        }
        fclose($handle);
    }, 'clientes_template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->middleware('throttle:template');

// Download do template CSV de unidades (colunas reais da planilha do usuário)
Route::get('/download/unidades-template', function () {
    $linhas = [
        ['identificacao','VALOR AVALIADO','Valor de Tabela','ANDAR','TIPOLOGIA','ÁREA PRIVATIVA','TIPO','VAGA DE GARAGEM','status'],
        ['105 A','R$ 205.000','R$ 210.000','TÉRREO','2QTS 2WCS','52,40','TÉRREO /A','1 VAGA','disponivel'],
        ['205 B','R$ 231.000','R$ 236.000','1º','2QTS 2WCS','52,40','1º /B','1 VAGA','disponivel'],
        ['101','R$ 240.000','R$ 245.000','TÉRREO','2QTS 2WCS','52.62','TÉRREO /A','1 VAGA','reservado'],
    ];

    return response()->streamDownload(function () use ($linhas) {
        $handle = fopen('php://output', 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        foreach ($linhas as $linha) {
            fwrite($handle, implode(';', $linha)."\n");
        }
        fclose($handle);
    }, 'unidades_template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->middleware('throttle:template');

