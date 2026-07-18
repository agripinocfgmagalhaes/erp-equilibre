<?php
namespace App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource;
use Filament\Resources\Pages\CreateRecord;
class CreateRequisicaoCompra extends CreateRecord
{
    protected static string $resource = RequisicaoCompraResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
