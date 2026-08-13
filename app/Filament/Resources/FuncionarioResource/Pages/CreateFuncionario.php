<?php
namespace App\Filament\Resources\FuncionarioResource\Pages;
use App\Filament\Resources\FuncionarioResource;
use Filament\Resources\Pages\CreateRecord;
class CreateFuncionario extends CreateRecord
{
    protected static string $resource = FuncionarioResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
