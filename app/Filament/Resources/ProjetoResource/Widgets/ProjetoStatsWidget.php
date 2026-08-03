<?php
namespace App\Filament\Resources\ProjetoResource\Widgets;
use App\Models\Projeto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjetoStatsWidget extends BaseWidget
{
    public ?Projeto $record = null;
    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        $projeto = $this->record;
        if (! $projeto) return [];

        $vendidas = $projeto->unidades()->where('status', 'vendido')->count();
        $disponiveis = $projeto->unidades()->where('status', 'disponivel')->count();
        $total = $projeto->unidades()->count();
        $vgv = $projeto->unidades()->sum('valor_tabela');

        return [
            Stat::make('Avanço Físico', $projeto->avancoFisico().'%')
                ->color(fn () => match (true) { $projeto->avancoFisico() >= 100 => 'success', $projeto->avancoFisico() >= 50 => 'info', $projeto->avancoFisico() > 0 => 'warning', default => 'gray' })
                ->icon('heroicon-o-wrench-screwdriver'),
            Stat::make('Avanço Financeiro', $projeto->valor_orcamento ? $projeto->avancoFinanceiro().'%' : 'sem orçamento')
                ->color(fn () => match (true) { $projeto->avancoFinanceiro() >= 100 => 'success', $projeto->avancoFinanceiro() >= 50 => 'info', $projeto->avancoFinanceiro() > 0 => 'warning', default => 'gray' })
                ->icon('heroicon-o-banknotes'),
            Stat::make('Unidades', "{$vendidas} vendidas / {$disponiveis} disponíveis")
                ->description("Total: {$total} unidades")->color('gray')->icon('heroicon-o-home'),
            Stat::make('VGV de Tabela', 'R$ '.number_format((float) $vgv, 2, ',', '.'))
                ->color('info')->icon('heroicon-o-document-text'),
        ];
    }
}
