<?php
namespace App\Filament\Resources\ServicoResource\Pages;
use Filament\Support\Enums\Width;
use Asmit\ResizedColumn\HasResizableColumn;
use App\Filament\Resources\ServicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListServicos extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ServicoResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Serviço')]; }
}
