<?php
namespace App\Filament\Resources\UnidadeResource\Pages;

use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\UnidadeResource;
use Filament\Resources\Pages\ListRecords;

class ListUnidades extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = UnidadeResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Unidade')]; }
}
