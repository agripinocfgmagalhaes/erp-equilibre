<?php
namespace App\Filament\Resources\ProdutoResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListProdutos extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ProdutoResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Produto')]; }
}
