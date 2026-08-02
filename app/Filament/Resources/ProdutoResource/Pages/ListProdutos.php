<?php
namespace App\Filament\Resources\ProdutoResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListProdutos extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ProdutoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Produto')]; }
}
