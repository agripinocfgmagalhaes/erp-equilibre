<?php
namespace App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListRequisicoesCompra extends ListRecords
{
    protected static string $resource = RequisicaoCompraResource::class;
    protected ?string $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->modalWidth('6xl')->label('+ Nova Requisição')]; }
}
