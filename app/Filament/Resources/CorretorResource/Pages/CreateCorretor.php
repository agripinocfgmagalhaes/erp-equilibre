<?php
namespace App\Filament\Resources\CorretorResource\Pages;
use App\Filament\Resources\CorretorResource;
use Filament\Resources\Pages\CreateRecord;
class CreateCorretor extends CreateRecord
{
    protected static string $resource = CorretorResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
