#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

RESOURCE="app/Filament/Resources/ProjetoResource.php"
cp "$RESOURCE" "$RESOURCE.bak3"

php -r '
$p = "app/Filament/Resources/ProjetoResource.php";
$s = file_get_contents($p);

$old = "            Section::make(\x27Unidades\x27)
                ->headerActions(fn (?Projeto \$record) => \$record ? [
                    ImportAction::make(\x27importarUnidades\x27)->label(\x27Importar Planilha\x27)->importer(UnidadeImporter::class)->options([\x27projeto_id\x27 => \$record->id]),
                ] : [])
                ->schema([";
$new = "            Section::make(\x27Unidades\x27)
                ->headerActions([
                    ImportAction::make(\x27importarUnidades\x27)->label(\x27Importar Planilha\x27)->importer(UnidadeImporter::class)
                        ->visible(fn (?Projeto \$record) => (bool) \$record)
                        ->options(fn (?Projeto \$record) => [\x27projeto_id\x27 => \$record?->id]),
                ])
                ->schema([";
if (strpos($s, $old) === false) { echo "NAO CASOU\n"; exit(1); }
file_put_contents($p, str_replace($old, $new, $s));
echo "OK\n";
'

php -l "$RESOURCE"
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true
echo "Feito."
