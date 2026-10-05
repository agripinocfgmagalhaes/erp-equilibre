<?php
namespace App\Filament\Pages;
use App\Filament\Resources\ContaReceberResource\Widgets\ContaReceberStatsWidget;
use App\Filament\Resources\ContaReceberResource\Widgets\RecebiveisPorMesChartWidget;
use App\Filament\Resources\ContaReceberResource\Widgets\ContaReceberAgingChartWidget;
use App\Filament\Resources\ContaReceberResource\Widgets\MaioresDevedoresWidget;
use Filament\Pages\Page;
class ContaReceberDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard Recebíveis';
    protected static ?string $title = 'Dashboard de Contas a Receber';
    protected static string|\UnitEnum|null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 0;
    protected static ?string $slug = 'contas-receber-dashboard';

    protected string $view = 'filament.pages.blank';
    protected \Filament\Support\Enums\Width|string|null $maxContentWidth = 'full';

    protected function getHeaderWidgets(): array
    {
        return [
            ContaReceberStatsWidget::class,
            RecebiveisPorMesChartWidget::class,
            ContaReceberAgingChartWidget::class,
            MaioresDevedoresWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }
}
