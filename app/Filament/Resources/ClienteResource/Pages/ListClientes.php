<?php
namespace App\Filament\Resources\ClienteResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListClientes extends ListRecords
{
    use HasToggleableTable, HasResizableColumn;
    protected static string $resource = ClienteResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Cliente')]; }
}
