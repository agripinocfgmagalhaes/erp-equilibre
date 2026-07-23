<?php
namespace App\Filament\Resources\ContaBancariaResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ContaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasBancarias extends ListRecords
{
    protected static string $resource = ContaBancariaResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->slideOver()->label('+ Nova Conta Bancária')]; }
}
