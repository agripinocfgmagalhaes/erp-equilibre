<?php
namespace App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource;
use Filament\Support\Enums\Width;
use Filament\Resources\Pages\ViewRecord;
class ViewRequisicaoCompra extends ViewRecord
{
    protected Width|string|null $maxContentWidth = 'full';
    protected static string $resource = RequisicaoCompraResource::class;
}
