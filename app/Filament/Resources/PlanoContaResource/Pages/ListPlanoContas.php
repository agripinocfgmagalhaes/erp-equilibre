<?php
namespace App\Filament\Resources\PlanoContaResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\PlanoContaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPlanoContas extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = PlanoContaResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Conta')]; }
}
