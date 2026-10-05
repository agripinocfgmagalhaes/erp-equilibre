<?php
namespace App\Filament\Resources\FolhaFechamentoResource\Pages;
use App\Filament\Resources\FolhaFechamentoResource;
use Filament\Resources\Pages\ListRecords;
class ListFolhaFechamentos extends ListRecords
{
    protected static string $resource = FolhaFechamentoResource::class;
    protected function getHeaderActions(): array { return []; }
}
