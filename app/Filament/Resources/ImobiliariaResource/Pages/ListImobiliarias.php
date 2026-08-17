<?php
namespace App\Filament\Resources\ImobiliariaResource\Pages;
use App\Filament\Resources\ImobiliariaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListImobiliarias extends ListRecords
{
    protected static string $resource = ImobiliariaResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->slideOver()];
    }
}
