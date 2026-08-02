<?php
namespace App\Filament\Resources\ContaBancariaResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasBancarias extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ContaBancariaResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Conta Bancária')]; }
}
