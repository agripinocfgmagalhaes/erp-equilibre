<?php
namespace App\Filament\Resources\CorretorResource\Pages;
use App\Filament\Resources\CorretorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListCorretores extends ListRecords
{
    protected static string $resource = CorretorResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->label('+ Novo Corretor')]; }
}
