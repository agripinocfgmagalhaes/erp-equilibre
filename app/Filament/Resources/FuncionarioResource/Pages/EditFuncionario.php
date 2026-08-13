<?php
namespace App\Filament\Resources\FuncionarioResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\FuncionarioResource;
use Filament\Resources\Pages\EditRecord;
class EditFuncionario extends EditRecord
{
    protected static string $resource = FuncionarioResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
