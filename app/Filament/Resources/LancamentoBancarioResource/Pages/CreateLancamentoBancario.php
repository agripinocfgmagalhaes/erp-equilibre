<?php
namespace App\Filament\Resources\LancamentoBancarioResource\Pages;
use App\Filament\Resources\LancamentoBancarioResource;
use Filament\Resources\Pages\CreateRecord;
class CreateLancamentoBancario extends CreateRecord
{
    protected static string $resource = LancamentoBancarioResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
