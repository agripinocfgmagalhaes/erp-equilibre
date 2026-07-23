<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\FasePadraoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditFasePadrao extends EditRecord
{
    protected static string $resource = FasePadraoResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
