<?php
namespace App\Filament\Resources\ProjetoResource\Pages;
use App\Filament\Resources\ProjetoResource;
use Filament\Support\Enums\Width;
use Filament\Resources\Pages\CreateRecord;
class CreateProjeto extends CreateRecord
{
    protected Width|string|null $maxContentWidth = 'full';
    protected static string $resource = ProjetoResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
