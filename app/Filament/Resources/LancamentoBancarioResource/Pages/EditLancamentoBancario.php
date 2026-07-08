<?php
namespace App\Filament\Resources\LancamentoBancarioResource\Pages;
use App\Filament\Resources\LancamentoBancarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditLancamentoBancario extends EditRecord
{
    protected static string $resource = LancamentoBancarioResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
