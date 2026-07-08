<?php
namespace App\Filament\Resources\PlanoContaResource\Pages;
use App\Filament\Resources\PlanoContaResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePlanoConta extends CreateRecord
{
    protected static string $resource = PlanoContaResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
