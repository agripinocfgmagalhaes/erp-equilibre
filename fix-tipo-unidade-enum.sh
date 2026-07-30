#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

RESOURCE="app/Filament/Resources/ProjetoResource.php"
cp "$RESOURCE" "$RESOURCE.bak5"

php -r '
$p = "app/Filament/Resources/ProjetoResource.php";
$s = file_get_contents($p);

$old = "TextInput::make(\x27tipo\x27)->label(\x27Tipo\x27)->maxLength(50),";
$new = "Select::make(\x27tipo\x27)->label(\x27Tipo\x27)->native(false)->default(\x27apartamento\x27)
                            ->options([\x27apartamento\x27 => \x27Apartamento\x27, \x27casa\x27 => \x27Casa\x27, \x27terreno\x27 => \x27Terreno\x27, \x27comercial\x27 => \x27Comercial\x27]),";
if (strpos($s, $old) === false) { echo "NAO CASOU\n"; exit(1); }
file_put_contents($p, str_replace($old, $new, $s));
echo "OK\n";
'

php -l "$RESOURCE"
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true
echo "Feito."
