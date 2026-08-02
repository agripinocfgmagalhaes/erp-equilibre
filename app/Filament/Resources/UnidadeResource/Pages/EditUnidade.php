<?php
namespace App\Filament\Resources\UnidadeResource\Pages;

use App\Filament\Resources\UnidadeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditUnidade extends EditRecord
{
    protected static string $resource = UnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
