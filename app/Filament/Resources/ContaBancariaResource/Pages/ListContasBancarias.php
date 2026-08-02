<?php
namespace App\Filament\Resources\ContaBancariaResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasBancarias extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ContaBancariaResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Conta Bancária')]; }
}
