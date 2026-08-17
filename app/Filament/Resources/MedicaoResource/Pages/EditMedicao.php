<?php
namespace App\Filament\Resources\MedicaoResource\Pages;
use App\Filament\Resources\MedicaoResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditMedicao extends EditRecord
{
    protected static string $resource = MedicaoResource::class;
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->visible(fn ($record) => $record->status === 'rascunho')];
    }
}
