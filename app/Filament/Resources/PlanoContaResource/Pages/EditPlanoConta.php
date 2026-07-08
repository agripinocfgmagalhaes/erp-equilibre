<?php
namespace App\Filament\Resources\PlanoContaResource\Pages;
use App\Filament\Resources\PlanoContaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPlanoConta extends EditRecord
{
    protected static string $resource = PlanoContaResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
