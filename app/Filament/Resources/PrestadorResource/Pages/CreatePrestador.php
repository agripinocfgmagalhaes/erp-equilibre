<?php
namespace App\Filament\Resources\PrestadorResource\Pages;
use App\Filament\Resources\PrestadorResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePrestador extends CreateRecord
{
    protected static string $resource = PrestadorResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
