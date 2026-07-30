#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

RESOURCE="app/Filament/Resources/ProjetoResource.php"
cp "$RESOURCE" "$RESOURCE.bak4"

php -r '
$p = "app/Filament/Resources/ProjetoResource.php";
$s = file_get_contents($p);

if (strpos($s, "use Filament\\\\Support\\\\RawJs;") === false) {
    $old0 = "use App\\\\Filament\\\\Imports\\\\ProjetoImporter;";
    $new0 = "use Filament\\\\Support\\\\RawJs;\nuse App\\\\Filament\\\\Imports\\\\ProjetoImporter;";
    if (strpos($s, $old0) === false) { echo "NAO CASOU: import RawJs\n"; exit(1); }
    $s = str_replace($old0, $new0, $s);
}

$old = "TableColumn::make(\x27Valor Tabela\x27)->width(\x27150px\x27), TableColumn::make(\x27Status\x27)->width(\x27120px\x27)])
                    ->schema([
                        TextInput::make(\x27identificacao\x27)->label(\x27ID\x27)->required()->maxLength(20),
                        TextInput::make(\x27tipo\x27)->label(\x27Tipo\x27)->maxLength(50),
                        TextInput::make(\x27area\x27)->label(\x27Área\x27)->numeric()->step(0.01),
                        TextInput::make(\x27valor_tabela\x27)->label(\x27Valor\x27)->numeric()->prefix(\x27R\$\x27)->step(0.01)->default(0),
                        Select::make(\x27status\x27)->label(\x27Status\x27)->native(false)->default(\x27disponivel\x27)";
$new = "TableColumn::make(\x27Valor Tabela\x27)->width(\x27160px\x27), TableColumn::make(\x27Status\x27)->width(\x27170px\x27)])
                    ->schema([
                        TextInput::make(\x27identificacao\x27)->label(\x27ID\x27)->required()->maxLength(20),
                        TextInput::make(\x27tipo\x27)->label(\x27Tipo\x27)->maxLength(50),
                        TextInput::make(\x27area\x27)->label(\x27Área\x27)->numeric()->step(0.01),
                        TextInput::make(\x27valor_tabela\x27)->label(\x27Valor\x27)->numeric()->prefix(\x27R\$\x27)->step(0.01)->default(0)
                            ->mask(RawJs::make(\x27\$money(\$input, \\\',\\\', \\\'.\\\')\x27))->stripCharacters(\x27.\x27)
                            ->dehydrateStateUsing(fn (\$state) => \$state !== null ? (float) str_replace(\x27,\x27, \x27.\x27, \$state) : null)
                            ->formatStateUsing(fn (\$state) => \$state !== null ? number_format((float) \$state, 2, \x27,\x27, \x27.\x27) : null),
                        Select::make(\x27status\x27)->label(\x27Status\x27)->native(false)->default(\x27disponivel\x27)";
if (strpos($s, $old) === false) { echo "NAO CASOU: campo valor_tabela/status\n"; exit(1); }
$s = str_replace($old, $new, $s);

file_put_contents($p, $s);
echo "OK\n";
'

php -l "$RESOURCE"
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true
echo "Feito."
