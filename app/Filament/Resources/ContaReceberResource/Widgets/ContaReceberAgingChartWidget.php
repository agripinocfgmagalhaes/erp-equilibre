<?php
namespace App\Filament\Resources\ContaReceberResource\Widgets;
use App\Models\ContaReceber;
use Filament\Widgets\ChartWidget;
class ContaReceberAgingChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'Inadimplência por Faixa de Atraso';
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight = '220px';

    protected function getData(): array
    {
        $faixas = [
            '1-30 dias'  => ContaReceber::where('status', 'vencido')->whereBetween('data_vencimento', [now()->subDays(30), now()->subDay()])->sum('valor'),
            '31-60 dias' => ContaReceber::where('status', 'vencido')->whereBetween('data_vencimento', [now()->subDays(60), now()->subDays(31)])->sum('valor'),
            '61-90 dias' => ContaReceber::where('status', 'vencido')->whereBetween('data_vencimento', [now()->subDays(90), now()->subDays(61)])->sum('valor'),
            '90+ dias'   => ContaReceber::where('status', 'vencido')->where('data_vencimento', '<', now()->subDays(90))->sum('valor'),
        ];

        return [
            'datasets' => [[
                'label' => 'Valor vencido (R$)',
                'data' => array_values($faixas),
                'backgroundColor' => ['#fbbf24', '#fb923c', '#f87171', '#dc2626'],
            ]],
            'labels' => array_keys($faixas),
        ];
    }

    protected function getType(): string { return 'bar'; }
}
