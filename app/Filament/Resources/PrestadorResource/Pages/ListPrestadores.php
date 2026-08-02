<?php
namespace App\Filament\Resources\PrestadorResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\PrestadorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPrestadores extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = PrestadorResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Prestador')]; }
}
