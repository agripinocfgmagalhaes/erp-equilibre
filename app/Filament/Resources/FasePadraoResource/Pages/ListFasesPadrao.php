<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\FasePadraoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListFasesPadrao extends ListRecords
{
    protected static string $resource = FasePadraoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Fase')]; }
}
