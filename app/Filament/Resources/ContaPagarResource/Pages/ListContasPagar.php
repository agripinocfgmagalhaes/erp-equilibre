<?php
namespace App\Filament\Resources\ContaPagarResource\Pages;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaPagarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasPagar extends ListRecords
{
    protected static string $resource = ContaPagarResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título')]; }
}
