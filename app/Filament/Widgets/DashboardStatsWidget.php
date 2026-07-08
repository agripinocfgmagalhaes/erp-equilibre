<?php
namespace App\Filament\Widgets;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\ContratoVenda;
use App\Models\Unidade;
use App\Models\PedidoCompra;
use App\Models\ContaBancaria;
use App\Models\LancamentoBancario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;
    protected function getColumns(): int { return 3; }
    protected function getStats(): array
    {
        $cpVencidas    = ContaPagar::where('status', 'vencido')->sum('valor');
        $cpAberto      = ContaPagar::where('status', 'aberto')->sum('valor');
        $crAberto      = ContaReceber::where('status', 'aberto')->sum('valor');
        $crVencidas    = ContaReceber::where('status', 'vencido')->sum('valor');
        $vgv           = ContratoVenda::where('status', 'ativo')->sum('valor_venda');
        $totalUnidades = Unidade::count();
        $vendidas      = Unidade::where('status', 'vendido')->count();
        $disponiveis   = Unidade::where('status', 'disponivel')->count();
        $comissoes     = ContratoVenda::where('status', 'ativo')->sum('valor_comissao');
        $comprasAberto = PedidoCompra::whereIn('status', ['rascunho', 'aprovado'])->sum('valor_total');
        $saldoBancario = ContaBancaria::where('ativo', true)->sum('saldo_inicial')
            + LancamentoBancario::where('tipo', 'entrada')->sum('valor')
            - LancamentoBancario::where('tipo', 'saida')->sum('valor');
        return [
            Stat::make('CP Vencidas', 'R$ '.number_format($cpVencidas, 2, ',', '.'))->description('Em aberto: R$ '.number_format($cpAberto, 2, ',', '.'))->color($cpVencidas > 0 ? 'danger' : 'success')->icon('heroicon-o-arrow-up-circle'),
            Stat::make('CR a Receber', 'R$ '.number_format($crAberto, 2, ',', '.'))->description('Vencidas: R$ '.number_format($crVencidas, 2, ',', '.'))->color($crVencidas > 0 ? 'warning' : 'success')->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Saldo Bancário', 'R$ '.number_format($saldoBancario, 2, ',', '.'))->color($saldoBancario >= 0 ? 'success' : 'danger')->icon('heroicon-o-building-library'),
            Stat::make('VGV Realizado', 'R$ '.number_format($vgv, 2, ',', '.'))->description('Comissões: R$ '.number_format($comissoes, 2, ',', '.'))->color('info')->icon('heroicon-o-document-text'),
            Stat::make('Unidades', "{$vendidas} vendidas / {$disponiveis} disponíveis")->description("Total: {$totalUnidades} unidades")->color('gray')->icon('heroicon-o-home'),
            Stat::make('Compras em Aberto', 'R$ '.number_format($comprasAberto, 2, ',', '.'))->color('warning')->icon('heroicon-o-shopping-cart'),
        ];
    }
}
