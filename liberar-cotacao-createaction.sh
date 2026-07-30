#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

FILE="app/Filament/Resources/RequisicaoCompraResource/RelationManagers/CotacoesRelationManager.php"
cp "$FILE" "$FILE.bak"

php -r '
$p = "app/Filament/Resources/RequisicaoCompraResource/RelationManagers/CotacoesRelationManager.php";
$s = file_get_contents($p);
$old = "    public static function canViewForRecord(\$ownerRecord, string \$pageClass): bool
    {
        return in_array(\$ownerRecord->status, [\x27em_cotacao\x27, \x27cotada\x27, \x27pedido_gerado\x27]);
    }";
$new = $old . "

    public function isReadOnly(): bool
    {
        return false;
    }";
if (strpos($s, $old) !== false) {
    $s = str_replace($old, $new, $s);
    file_put_contents($p, $s);
    echo "OK: isReadOnly() adicionado\n";
} else {
    echo "NAO CASOU\n";
    exit(1);
}
'

php -l "$FILE"
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true
echo "Feito."
