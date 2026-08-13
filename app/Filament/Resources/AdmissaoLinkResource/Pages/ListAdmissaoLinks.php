<?php
namespace App\Filament\Resources\AdmissaoLinkResource\Pages;
use Filament\Actions\CreateAction;
use App\Filament\Resources\AdmissaoLinkResource;
use Filament\Resources\Pages\ListRecords;
class ListAdmissaoLinks extends ListRecords
{
    protected static string $resource = AdmissaoLinkResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('+ Novo Link')->slideOver()]; }
}
