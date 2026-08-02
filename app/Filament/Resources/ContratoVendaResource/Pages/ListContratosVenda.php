<?php
namespace App\Filament\Resources\ContratoVendaResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContratoVendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListContratosVenda extends ListRecords
{
    use HasToggleableTable, HasResizableColumn;
    protected static string $resource = ContratoVendaResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Contrato')]; }
}
