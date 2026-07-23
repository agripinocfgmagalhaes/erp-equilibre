<?php
namespace App\Filament\Resources\ProdutoResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditProduto extends EditRecord
{
    protected static string $resource = ProdutoResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
