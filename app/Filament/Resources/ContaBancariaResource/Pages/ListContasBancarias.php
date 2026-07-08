<?php
namespace App\Filament\Resources\ContaBancariaResource\Pages;
use App\Filament\Resources\ContaBancariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListContasBancarias extends ListRecords
{
    protected static string $resource = ContaBancariaResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->label('+ Nova Conta Bancária')]; }
}
