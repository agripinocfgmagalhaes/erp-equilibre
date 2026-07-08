<?php
namespace App\Filament\Resources\ContratoVendaResource\Pages;
use App\Filament\Resources\ContratoVendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContratosVenda extends ListRecords
{
    protected static string $resource = ContratoVendaResource::class;
    protected ?string $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Contrato')]; }
}
