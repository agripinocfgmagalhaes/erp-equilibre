<?php
namespace App\Filament\Resources\ContaReceberResource\Pages;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasReceber extends ListRecords
{
    protected static string $resource = ContaReceberResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título')]; }
}
