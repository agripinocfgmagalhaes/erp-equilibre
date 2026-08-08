<?php
namespace App\Filament\Resources\ProjetoResource\Pages;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Width;
use App\Filament\Resources\ProjetoResource;
use App\Filament\Resources\ProjetoResource\Widgets\ProjetoStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditProjeto extends EditRecord
{
    protected Width|string|null $maxContentWidth = 'full';
    protected static string $resource = ProjetoResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
    protected function getHeaderWidgets(): array { return [ProjetoStatsWidget::class]; }
}
