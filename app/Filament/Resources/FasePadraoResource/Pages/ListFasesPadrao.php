<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use App\Filament\Resources\FasePadraoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListFasesPadrao extends ListRecords
{
    protected static string $resource = FasePadraoResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->label('+ Nova Fase')]; }
}
