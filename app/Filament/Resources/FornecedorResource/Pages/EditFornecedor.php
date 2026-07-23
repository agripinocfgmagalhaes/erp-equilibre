<?php
namespace App\Filament\Resources\FornecedorResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\FornecedorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditFornecedor extends EditRecord
{
    protected static string $resource = FornecedorResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
