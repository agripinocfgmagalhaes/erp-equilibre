<?php
namespace App\Filament\Resources\PedidoCompraResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPedidosCompra extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = PedidoCompraResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('5xl')->label('+ Novo Pedido')]; }
}
