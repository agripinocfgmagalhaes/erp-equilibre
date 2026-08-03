<?php
namespace App\Filament\Resources\OrdenServicoResource\Pages;
use App\Filament\Resources\OrdenServicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListOrdenServico extends ListRecords
{
    protected static string $resource = OrdenServicoResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('+ Ordem de Serviço')->slideOver()];
    }
}
