<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\FasePadraoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListFasesPadrao extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = FasePadraoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Fase')]; }
}
