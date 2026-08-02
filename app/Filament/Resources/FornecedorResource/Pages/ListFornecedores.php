<?php
namespace App\Filament\Resources\FornecedorResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\FornecedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListFornecedores extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = FornecedorResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Fornecedor')]; }
}
