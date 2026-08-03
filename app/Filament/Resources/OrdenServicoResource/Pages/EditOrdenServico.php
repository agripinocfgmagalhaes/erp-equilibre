<?php
namespace App\Filament\Resources\OrdenServicoResource\Pages;
use App\Filament\Resources\OrdenServicoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\EditRecord;
class EditOrdenServico extends EditRecord
{
    protected static string $resource = OrdenServicoResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
