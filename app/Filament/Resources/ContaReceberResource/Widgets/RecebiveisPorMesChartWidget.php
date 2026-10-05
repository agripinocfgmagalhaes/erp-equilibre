<?php
namespace App\Filament\Resources\ContaReceberResource\Widgets;
use App\Models\ContaReceber;
use Filament\Widgets\ChartWidget;
class RecebiveisPorMesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Recebíveis por Mês';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $meses = collect(range(-3, 8))->map(fn ($i) => now()->addMonthsNoOverflow($i)->startOfMonth());

        $previsto = [];
        $recebido = [];
        $labels = [];

        foreach ($meses as $mes) {
            $labels[] = ucfirst($mes->translatedFormat('M/y'));

            $previsto[] = (float) ContaReceber::whereIn('status', ['aberto', 'vencido'])
                ->whereYear('data_vencimento', $mes->year)
                ->whereMonth('data_vencimento', $mes->month)
                ->sum('valor');

            $recebido[] = (float) ContaReceber::where('status', 'recebido')
                ->whereYear('data_recebimento', $mes->year)
                ->whereMonth('data_recebimento', $mes->month)
                ->sum('valor_recebido');
        }

        return [
            'datasets' => [
                ['label' => 'Recebido', 'data' => $recebido, 'backgroundColor' => '#10b981'],
                ['label' => 'Previsto (em aberto/vencido)', 'data' => $previsto, 'backgroundColor' => '#3b82f6'],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => false],
                'y' => ['stacked' => false, 'beginAtZero' => true],
            ],
        ];
    }
}
