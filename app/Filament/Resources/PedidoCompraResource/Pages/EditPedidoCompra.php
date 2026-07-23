<?php
namespace App\Filament\Resources\PedidoCompraResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPedidoCompra extends EditRecord
{
    protected static string $resource = PedidoCompraResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
