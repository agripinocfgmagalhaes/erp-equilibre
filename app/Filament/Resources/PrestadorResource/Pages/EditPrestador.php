<?php
namespace App\Filament\Resources\PrestadorResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\PrestadorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPrestador extends EditRecord
{
    protected static string $resource = PrestadorResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
