#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

RESOURCE="app/Filament/Resources/UserResource.php"

if [ ! -f "$RESOURCE" ]; then
  echo "Arquivo não encontrado: $RESOURCE"
  exit 1
fi

cp "$RESOURCE" "$RESOURCE.bak"

php -r '
$p = "app/Filament/Resources/UserResource.php";
$s = file_get_contents($p);

$old = "    public static function getPages(): array
    {
        return [\x27index\x27 => ListUsers::route(\x27/\x27)];
    }
}";

$new = "    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole(\x27admin\x27) ?? false;
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(\x27admin\x27) ?? false;
    }
    public static function canEdit(\$record): bool
    {
        return auth()->user()?->hasRole(\x27admin\x27) ?? false;
    }
    public static function canDelete(\$record): bool
    {
        return auth()->user()?->hasRole(\x27admin\x27) ?? false;
    }
    public static function getPages(): array
    {
        return [\x27index\x27 => ListUsers::route(\x27/\x27)];
    }
}";

if (strpos($s, $old) !== false) {
    $s = str_replace($old, $new, $s);
    file_put_contents($p, $s);
    echo "OK: restrição aplicada\n";
} else {
    echo "NAO CASOU\n";
    exit(1);
}
'

echo "Validando sintaxe..."
php -l "$RESOURCE"

echo "Limpando cache..."
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true

echo "Feito. Backup em $RESOURCE.bak"
