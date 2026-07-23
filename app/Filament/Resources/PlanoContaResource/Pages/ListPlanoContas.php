<?php
namespace App\Filament\Resources\PlanoContaResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\PlanoContaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPlanoContas extends ListRecords
{
    protected static string $resource = PlanoContaResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Conta')]; }
}
