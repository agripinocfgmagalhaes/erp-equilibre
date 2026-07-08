<?php
namespace App\Filament\Resources\ContaBancariaResource\Pages;
use App\Filament\Resources\ContaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditContaBancaria extends EditRecord
{
    protected static string $resource = ContaBancariaResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
