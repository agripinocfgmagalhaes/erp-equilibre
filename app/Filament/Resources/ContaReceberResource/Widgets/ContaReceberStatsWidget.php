<?php
namespace App\Filament\Resources\ContaReceberResource\Widgets;
use App\Models\ContaReceber;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class ContaReceberStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = null;

    protected function getColumns(): int { return 4; }

    protected function getStats(): array
    {
        // saldo = valor - valor_recebido (parcial conta só pelo que falta); "vencido" definido pela data, nao só pelo status
        $saldoExpr = 'valor - valor_recebido';
        $baseAberta = ContaReceber::whereIn('status', ['aberto', 'vencido', 'parcial']);

        $aberto = (clone $baseAberta)->where('data_vencimento', '>=', now()->toDateString())->sum(DB::raw($saldoExpr));
        $vencido = (clone $baseAberta)->where('data_vencimento', '<', now()->toDateString())->sum(DB::raw($saldoExpr));
        $qtdVencido = (clone $baseAberta)->where('data_vencimento', '<', now()->toDateString())->count();
        $totalCarteira = $aberto + $vencido;

        $recebidoMes = ContaReceber::whereIn('status', ['recebido', 'parcial'])
            ->whereMonth('data_recebimento', now()->month)
            ->whereYear('data_recebimento', now()->year)
            ->sum('valor_recebido');

        $vencendo7dias = (clone $baseAberta)
            ->whereBetween('data_vencimento', [now(), now()->addDays(7)])
            ->sum(DB::raw($saldoExpr));

        $inadimplencia = $totalCarteira > 0 ? ($vencido / $totalCarteira) * 100 : 0;

        return [
            Stat::make('Em Aberto', 'R$ '.number_format($aberto, 2, ',', '.'))
                ->description('Carteira total: R$ '.number_format($totalCarteira, 2, ',', '.'))
                ->color('info')->icon('heroicon-o-clock'),

            Stat::make('Vencido', 'R$ '.number_format($vencido, 2, ',', '.'))
                ->description($qtdVencido.' título(s) em atraso')
                ->color($vencido > 0 ? 'danger' : 'success')->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Inadimplência', number_format($inadimplencia, 1, ',', '.').'%')
                ->description('% da carteira vencida')
                ->color($inadimplencia > 10 ? 'danger' : ($inadimplencia > 5 ? 'warning' : 'success'))
                ->icon('heroicon-o-chart-pie'),

            Stat::make('Recebido no Mês', 'R$ '.number_format($recebidoMes, 2, ',', '.'))
                ->description('Vence em 7 dias: R$ '.number_format($vencendo7dias, 2, ',', '.'))
                ->color('success')->icon('heroicon-o-banknotes'),
        ];
    }
}
