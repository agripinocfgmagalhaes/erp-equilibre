<?php
namespace App\Filament\Resources\FuncionarioResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\FuncionarioResource;
use Filament\Resources\Pages\ListRecords;
class ListFuncionarios extends ListRecords
{
    protected static string $resource = FuncionarioResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
