<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/boletos/{contaReceber}/pdf', [\App\Http\Controllers\BoletoController::class, 'pdf'])->name('boleto.pdf')->middleware('auth');
Route::get('/boletos/publico/{codigoSolicitacao}/pdf', [\App\Http\Controllers\BoletoController::class, 'pdfPublico'])->name('boleto.pdf.publico');
Route::post('/webhooks/inter/cobranca', [\App\Http\Controllers\InterWebhookController::class, 'receber'])->name('webhook.inter.cobranca');Route::get('/meus-boletos', [\App\Http\Controllers\ClientePortalController::class, 'boletos'])->name('portal.boletos');
