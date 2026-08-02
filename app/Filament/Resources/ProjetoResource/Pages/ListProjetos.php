<?php
namespace App\Filament\Resources\ProjetoResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListProjetos extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = ProjetoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Empreendimento')]; }
}
