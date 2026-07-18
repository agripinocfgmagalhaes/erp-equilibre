<?php
namespace App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditRequisicaoCompra extends EditRecord
{
    protected static string $resource = RequisicaoCompraResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
