<?php
namespace App\Filament\Resources\ContaReceberResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditContaReceber extends EditRecord
{
    protected static string $resource = ContaReceberResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
