<?php
namespace App\Filament\Resources\AdmissaoLinkResource\Pages;
use App\Filament\Resources\AdmissaoLinkResource;
use Filament\Resources\Pages\CreateRecord;
class CreateAdmissaoLink extends CreateRecord
{
    protected static string $resource = AdmissaoLinkResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
