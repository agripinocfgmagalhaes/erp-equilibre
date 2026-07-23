<?php
namespace App\Filament\Resources\ProdutoResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListProdutos extends ListRecords
{
    protected static string $resource = ProdutoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Produto')]; }
}
