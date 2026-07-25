<?php
namespace App\Filament\Resources\ContaPagarResource\Pages;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaPagarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListContasPagar extends ListRecords
{
    use HasToggleableTable;
    protected static string $resource = ContaPagarResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título')]; }
}
