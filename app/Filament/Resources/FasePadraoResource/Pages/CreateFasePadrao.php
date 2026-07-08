<?php
namespace App\Filament\Resources\FasePadraoResource\Pages;
use App\Filament\Resources\FasePadraoResource;
use Filament\Resources\Pages\CreateRecord;
class CreateFasePadrao extends CreateRecord
{
    protected static string $resource = FasePadraoResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
