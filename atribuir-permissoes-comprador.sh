#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

echo "1) Garantindo que todas as permissões do Shield existem no banco..."
php artisan shield:generate --all --panel=admin --no-interaction

echo ""
echo "1b) Conferindo se o role 'admin' tem todas as permissões..."
php artisan tinker --execute="
\$admin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
\$total = \Spatie\Permission\Models\Permission::count();
\$tem = \$admin->permissions()->count();
echo \"Role admin tem \$tem de \$total permissões.\" . PHP_EOL;
if (\$tem < \$total) {
    \$admin->givePermissionTo(\Spatie\Permission\Models\Permission::all());
    echo 'Corrigido: admin agora tem TODAS as permissões.' . PHP_EOL;
}
"

echo ""
echo "2) Atribuindo permissões de Compras ao role 'comprador'..."
php artisan tinker --execute="
\$role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'comprador', 'guard_name' => 'web']);
\$modulos = ['fornecedor', 'pedido::compra', 'prestador', 'produto', 'requisicao::compra'];
\$acoes = ['view_any', 'view', 'create', 'update'];
\$permissoes = [];
foreach (\$modulos as \$m) {
    foreach (\$acoes as \$a) {
        \$permissoes[] = \$a . '_' . \$m;
    }
}
\$role->givePermissionTo(\$permissoes);
echo 'Permissões atribuídas a comprador: ' . implode(', ', \$permissoes) . PHP_EOL;
"

echo ""
echo "3) Conferindo o que o role 'comprador' tem agora:"
php artisan tinker --execute="
\$role = \Spatie\Permission\Models\Role::where('name','comprador')->first();
echo \$role->permissions->pluck('name')->implode(', ') . PHP_EOL;
"

echo ""
echo "4) Limpando cache de permissões e do Filament..."
php artisan permission:cache-reset
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true

echo ""
echo "Feito. Peça pro usuário deslogar e logar de novo pra sessão pegar as novas permissões."
