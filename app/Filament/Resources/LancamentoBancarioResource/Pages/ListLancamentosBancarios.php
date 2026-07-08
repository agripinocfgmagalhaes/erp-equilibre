<?php
namespace App\Filament\Resources\LancamentoBancarioResource\Pages;
use App\Filament\Resources\LancamentoBancarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListLancamentosBancarios extends ListRecords
{
    protected static string $resource = LancamentoBancarioResource::class;
    protected ?string $maxContentWidth = 'full';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->slideOver()->label('+ Novo Lançamento')]; }
}
