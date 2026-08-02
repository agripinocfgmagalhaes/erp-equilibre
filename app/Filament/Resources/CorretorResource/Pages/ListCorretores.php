<?php
namespace App\Filament\Resources\CorretorResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\CorretorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListCorretores extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = CorretorResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Corretor')]; }
}
