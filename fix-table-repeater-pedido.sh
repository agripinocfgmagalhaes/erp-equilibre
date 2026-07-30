#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

RESOURCE="app/Filament/Resources/PedidoCompraResource.php"
cp "$RESOURCE" "$RESOURCE.bak"

php -r '
$p = "app/Filament/Resources/PedidoCompraResource.php";
$s = file_get_contents($p);

$old1 = "use Awcodes\\TableRepeater\\Components\\TableRepeater;\nuse Awcodes\\TableRepeater\\Header;";
$new1 = "use Filament\\Forms\\Components\\Repeater;\nuse Filament\\Forms\\Components\\Repeater\\TableColumn;";
if (strpos($s, $old1) === false) { echo "NAO CASOU: imports\n"; exit(1); }
$s = str_replace($old1, $new1, $s);

$old2 = "TableRepeater::make(\x27itens\x27)->relationship()->label(\x27\x27)\n                    ->headers([Header::make(\x27Produto\x27)->width(\x27200px\x27), Header::make(\x27Descrição\x27)->width(\x27200px\x27), Header::make(\x27Unid.\x27)->width(\x2780px\x27), Header::make(\x27Qtd.\x27)->width(\x27100px\x27), Header::make(\x27Valor Unit. (R\$)\x27)->width(\x27140px\x27), Header::make(\x27Total (R\$)\x27)->width(\x27140px\x27)])";
$new2 = "Repeater::make(\x27itens\x27)->relationship()->label(\x27\x27)\n                    ->table([TableColumn::make(\x27Produto\x27)->width(\x27200px\x27), TableColumn::make(\x27Descrição\x27)->width(\x27200px\x27), TableColumn::make(\x27Unid.\x27)->width(\x2780px\x27), TableColumn::make(\x27Qtd.\x27)->width(\x27100px\x27), TableColumn::make(\x27Valor Unit. (R\$)\x27)->width(\x27140px\x27), TableColumn::make(\x27Total (R\$)\x27)->width(\x27140px\x27)])";
if (strpos($s, $old2) === false) { echo "NAO CASOU: bloco itens\n"; exit(1); }
$s = str_replace($old2, $new2, $s);

$old3 = "            ])->columns(3),\n            Section::make(\x27Itens\x27)->schema([";
$new3 = "            ])->columns(3)->columnSpanFull(),\n            Section::make(\x27Itens\x27)->schema([";
if (strpos($s, $old3) === false) { echo "NAO CASOU: columnSpanFull Dados do Pedido\n"; exit(1); }
$s = str_replace($old3, $new3, $s);

$old4 = "                    }),\n            ]),";
$new4 = "                    }),\n            ])->columnSpanFull(),";
if (strpos($s, $old4) === false) { echo "NAO CASOU: columnSpanFull Itens\n"; exit(1); }
$s = str_replace($old4, $new4, $s);

file_put_contents($p, $s);
echo "OK: PedidoCompraResource corrigido\n";
'

php -l "$RESOURCE"
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true
echo "Feito."
