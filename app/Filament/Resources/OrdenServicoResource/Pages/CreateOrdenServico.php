<?php
namespace App\Filament\Resources\OrdenServicoResource\Pages;
use App\Filament\Resources\OrdenServicoResource;
use Filament\Resources\Pages\CreateRecord;
class CreateOrdenServico extends CreateRecord
{
    protected static string $resource = OrdenServicoResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
