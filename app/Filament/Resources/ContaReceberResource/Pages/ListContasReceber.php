<?php
namespace App\Filament\Resources\ContaReceberResource\Pages;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasReceber extends ListRecords
{
    protected static string $resource = ContaReceberResource::class;
    protected ?string $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título')]; }
}
