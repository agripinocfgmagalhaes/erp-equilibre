<?php
namespace App\Filament\Resources\ContratoVendaResource\Pages;
use App\Filament\Resources\ContratoVendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditContratoVenda extends EditRecord
{
    protected static string $resource = ContratoVendaResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
