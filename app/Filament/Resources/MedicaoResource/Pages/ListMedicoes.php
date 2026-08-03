<?php
namespace App\Filament\Resources\MedicaoResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use App\Filament\Resources\MedicaoResource;
use Filament\Resources\Pages\ListRecords;
class ListMedicoes extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = MedicaoResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return []; }
}
