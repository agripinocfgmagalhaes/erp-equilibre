<?php
namespace App\Filament\Resources\OrdenServicoResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use App\Filament\Resources\OrdenServicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListOrdenServico extends ListRecords
{
    use HasToggleableTable, HasResizableColumn;
    protected static string $resource = OrdenServicoResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('+ Ordem de Serviço')->slideOver()];
    }
}
