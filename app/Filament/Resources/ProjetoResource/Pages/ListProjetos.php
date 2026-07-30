<?php
namespace App\Filament\Resources\ProjetoResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListProjetos extends ListRecords
{
    protected static string $resource = ProjetoResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Novo Empreendimento')]; }
}
