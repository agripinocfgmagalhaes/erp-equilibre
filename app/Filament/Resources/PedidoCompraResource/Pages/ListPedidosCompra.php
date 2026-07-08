<?php
namespace App\Filament\Resources\PedidoCompraResource\Pages;
use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPedidosCompra extends ListRecords
{
    protected static string $resource = PedidoCompraResource::class;
    protected ?string $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->modalWidth('5xl')->label('+ Novo Pedido')]; }
}
