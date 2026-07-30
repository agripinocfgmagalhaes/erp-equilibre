#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

MODEL="app/Models/RequisicaoCompra.php"
RESOURCE="app/Filament/Resources/RequisicaoCompraResource.php"
RELMGR="app/Filament/Resources/RequisicaoCompraResource/RelationManagers/CotacoesRelationManager.php"

cp "$MODEL" "$MODEL.bak"
cp "$RESOURCE" "$RESOURCE.bak"
cp "$RELMGR" "$RELMGR.bak"

php -r '
// 1) Model: adiciona gerarPedidoDireto()
$p = "app/Models/RequisicaoCompra.php";
$s = file_get_contents($p);
$old = "    public function selecionarVencedoraEGerarPedido(CotacaoCompra \$cotacao): PedidoCompra";
$new = "    public function gerarPedidoDireto(int \$fornecedorId, array \$itensPrecos, ?int \$prazoEntregaDias = null): PedidoCompra
    {
        \$pedido = PedidoCompra::create([
            \x27numero\x27 => PedidoCompra::gerarNumero(),
            \x27requisicao_compra_id\x27 => \$this->id,
            \x27cotacao_compra_id\x27 => null,
            \x27projeto_id\x27 => \$this->projeto_id,
            \x27fase_obra_id\x27 => \$this->fase_obra_id,
            \x27fornecedor_id\x27 => \$fornecedorId,
            \x27status\x27 => \x27aprovado\x27,
            \x27data_pedido\x27 => now(),
            \x27data_previsao_entrega\x27 => \$prazoEntregaDias ? now()->addDays(\$prazoEntregaDias) : null,
            \x27observacoes\x27 => \x27Gerado diretamente da Requisição \x27.\$this->numero.\x27 (sem cotação).\x27,
        ]);

        foreach (\$itensPrecos as \$itemId => \$valorUnitario) {
            \$itemReq = \$this->itens()->find(\$itemId);
            if (! \$itemReq) {
                continue;
            }
            ItemPedidoCompra::create([
                \x27pedido_compra_id\x27 => \$pedido->id,
                \x27produto_id\x27 => \$itemReq->produto_id,
                \x27descricao\x27 => \$itemReq->descricao,
                \x27unidade\x27 => \$itemReq->unidade ?? \x27UN\x27,
                \x27quantidade\x27 => \$itemReq->quantidade ?? 1,
                \x27valor_unitario\x27 => \$valorUnitario,
            ]);
        }

        \$pedido->recalcularTotal();
        \$this->update([\x27status\x27 => \x27pedido_gerado\x27]);

        return \$pedido;
    }

    public function selecionarVencedoraEGerarPedido(CotacaoCompra \$cotacao): PedidoCompra";
if (strpos($s, $old) === false) { echo "NAO CASOU: model\n"; exit(1); }
file_put_contents($p, str_replace($old, $new, $s));
echo "OK: model\n";

// 2) Resource: import Fornecedor
$p = "app/Filament/Resources/RequisicaoCompraResource.php";
$s = file_get_contents($p);
$old = "use App\\Models\\RequisicaoCompra;\nuse App\\Models\\Projeto;";
$new = "use App\\Models\\RequisicaoCompra;\nuse App\\Models\\Projeto;\nuse App\\Models\\Fornecedor;";
if (strpos($s, $old) === false) { echo "NAO CASOU: resource import\n"; exit(1); }
$s = str_replace($old, $new, $s);

// 3) Resource: nova action gerarPedidoSemCotacao
$old2 = "                ->successNotificationTitle(\x27Pedido de compra gerado a partir da cotação vencedora\x27),\n\n            EditAction::make()";
$new2 = "                ->successNotificationTitle(\x27Pedido de compra gerado a partir da cotação vencedora\x27),\n\n            Action::make(\x27gerarPedidoSemCotacao\x27)->label(\x27Gerar Pedido sem Cotação\x27)->icon(\x27heroicon-o-bolt\x27)->color(\x27gray\x27)\n                ->visible(fn (RequisicaoCompra \$record) => in_array(\$record->status, [\x27em_cotacao\x27, \x27cotada\x27]) && Auth::user()->hasAnyRole([\x27responsavel\x27, \x27admin\x27]))\n                ->schema(fn (RequisicaoCompra \$record) => [\n                    Select::make(\x27fornecedor_id\x27)->label(\x27Fornecedor\x27)\n                        ->options(Fornecedor::where(\x27ativo\x27, true)->pluck(\x27nome\x27, \x27id\x27))->searchable()->native(false)->required(),\n                    TextInput::make(\x27prazo_entrega_dias\x27)->label(\x27Prazo de Entrega (dias)\x27)->numeric()->minValue(0),\n                    Repeater::make(\x27itens_pedido\x27)->label(\x27Itens e Preços\x27)\n                        ->schema([\n                            Hidden::make(\x27item_id\x27),\n                            TextInput::make(\x27descricao\x27)->label(\x27Item\x27)->disabled()->dehydrated(false),\n                            TextInput::make(\x27valor_unitario\x27)->label(\x27Valor Unitário\x27)->numeric()->prefix(\x27R\$\x27)->step(0.01)->default(0)->required(),\n                        ])\n                        ->default(\$record->itens->map(fn (\$i) => [\x27item_id\x27 => \$i->id, \x27descricao\x27 => \$i->descricao, \x27valor_unitario\x27 => 0])->toArray())\n                        ->addable(false)->deletable(false)->reorderable(false)->columns(3)->columnSpanFull(),\n                ])\n                ->action(function (RequisicaoCompra \$record, array \$data) {\n                    \$itensPrecos = collect(\$data[\x27itens_pedido\x27])->pluck(\x27valor_unitario\x27, \x27item_id\x27)->toArray();\n                    \$record->gerarPedidoDireto(\$data[\x27fornecedor_id\x27], \$itensPrecos, \$data[\x27prazo_entrega_dias\x27] ?? null);\n                })\n                ->successNotificationTitle(\x27Pedido de compra gerado diretamente, sem cotação\x27),\n\n            EditAction::make()";
if (strpos($s, $old2) === false) { echo "NAO CASOU: resource action\n"; exit(1); }
$s = str_replace($old2, $new2, $s);
file_put_contents($p, $s);
echo "OK: resource\n";

// 4) RelationManager: auto-preencher itens da cotação
$p = "app/Filament/Resources/RequisicaoCompraResource/RelationManagers/CotacoesRelationManager.php";
$s = file_get_contents($p);
$old = "            Repeater::make(\x27itens\x27)->relationship()->label(\x27Preços por Item\x27)
                ->schema([
                    Select::make(\x27item_requisicao_compra_id\x27)->label(\x27Item\x27)
                        ->options(\$requisicao->itens->pluck(\x27descricao\x27, \x27id\x27))->required()->native(false),
                    TextInput::make(\x27valor_unitario\x27)->label(\x27Valor Unitário\x27)->numeric()->prefix(\x27R\$\x27)->step(0.01)->default(0)->required(),
                ])
                ->columns(2)->defaultItems(0)->addActionLabel(\x27+ Adicionar Preço\x27)->columnSpanFull(),";
$new = "            Repeater::make(\x27itens\x27)->relationship()->label(\x27Preços por Item\x27)
                ->schema([
                    Select::make(\x27item_requisicao_compra_id\x27)->label(\x27Item\x27)
                        ->options(\$requisicao->itens->pluck(\x27descricao\x27, \x27id\x27))->required()->native(false)
                        ->disabled()->dehydrated(true),
                    TextInput::make(\x27valor_unitario\x27)->label(\x27Valor Unitário\x27)->numeric()->prefix(\x27R\$\x27)->step(0.01)->default(0)->required(),
                ])
                ->default(\$requisicao->itens->map(fn (\$item) => [\x27item_requisicao_compra_id\x27 => \$item->id, \x27valor_unitario\x27 => 0])->toArray())
                ->columns(2)->addable(false)->deletable(false)->reorderable(false)->columnSpanFull(),";
if (strpos($s, $old) === false) { echo "NAO CASOU: relation manager\n"; exit(1); }
file_put_contents($p, str_replace($old, $new, $s));
echo "OK: relation manager\n";
'

echo ""
echo "Validando sintaxe..."
php -l "$MODEL"
php -l "$RESOURCE"
php -l "$RELMGR"

echo ""
echo "Limpando cache..."
php artisan optimize:clear
php artisan filament:cache-components 2>/dev/null || true

echo ""
echo "Feito. Backups: $MODEL.bak, $RESOURCE.bak, $RELMGR.bak"
