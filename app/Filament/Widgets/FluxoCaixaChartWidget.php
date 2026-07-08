<?php
namespace App\Filament\Widgets;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Filament\Widgets\ChartWidget;
class FluxoCaixaChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Fluxo de Caixa — Últimos 6 Meses';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';
    protected function getData(): array
    {
        $meses = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $labels = $meses->map(fn ($m) => $m->format('m/Y'))->toArray();
        $entradas = $meses->map(fn ($m) => (float) ContaReceber::whereYear('data_recebimento', $m->year)->whereMonth('data_recebimento', $m->month)->where('status', 'recebido')->sum('valor_recebido'))->toArray();
        $saidas = $meses->map(fn ($m) => (float) ContaPagar::whereYear('data_pagamento', $m->year)->whereMonth('data_pagamento', $m->month)->where('status', 'pago')->sum('valor_pago'))->toArray();
        return [
            'datasets' => [
                ['label' => 'Recebimentos', 'data' => $entradas, 'backgroundColor' => 'rgba(34,197,94,0.2)', 'borderColor' => 'rgb(34,197,94)', 'borderWidth' => 2, 'fill' => true],
                ['label' => 'Pagamentos', 'data' => $saidas, 'backgroundColor' => 'rgba(239,68,68,0.2)', 'borderColor' => 'rgb(239,68,68)', 'borderWidth' => 2, 'fill' => true],
            ],
            'labels' => $labels,
        ];
    }
    protected function getType(): string { return 'line'; }
}
