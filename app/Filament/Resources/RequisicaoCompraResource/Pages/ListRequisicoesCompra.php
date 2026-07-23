<?php
namespace App\Filament\Resources\RequisicaoCompraResource\Pages;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\RequisicaoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListRequisicoesCompra extends ListRecords
{
    protected static string $resource = RequisicaoCompraResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('6xl')->label('+ Nova Requisição')]; }
}
