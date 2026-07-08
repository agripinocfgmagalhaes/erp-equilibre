<?php
namespace App\Filament\Resources\CorretorResource\Pages;
use App\Filament\Resources\CorretorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditCorretor extends EditRecord
{
    protected static string $resource = CorretorResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
