<?php

return [
    'ambiente' => env('INTER_AMBIENTE', 'sandbox'),
    'client_id' => env('INTER_CLIENT_ID'),
    'client_secret' => env('INTER_CLIENT_SECRET'),
    'conta_corrente' => env('INTER_CONTA_CORRENTE'),
    'cert_path' => env('INTER_CERT_PATH'),
    'key_path' => env('INTER_KEY_PATH'),
    'cedente_cnpj' => env('INTER_CEDENTE_CNPJ'),
    'scope' => 'boleto-cobranca.write boleto-cobranca.read',
];
