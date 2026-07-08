<?php
namespace App\Filament\Resources\ContratoVendaResource\Pages;
use App\Filament\Resources\ContratoVendaResource;
use Filament\Resources\Pages\CreateRecord;
class CreateContratoVenda extends CreateRecord
{
    protected static string $resource = ContratoVendaResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
