<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use App\Filament\Resources\FasePadraoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditFasePadrao extends EditRecord
{
    protected static string $resource = FasePadraoResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
