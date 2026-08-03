<?php
namespace App\Filament\Widgets;
use App\Models\Projeto;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CurvaFisicoFinanceiraWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Curva Física-Financeira';

    public function table(Table $table): Table
    {
        return $table
            ->query(Projeto::query()->where('status', 'em_andamento'))
            ->columns([
                TextColumn::make('nome')->label('Empreendimento')->weight('medium'),
                TextColumn::make('avanco_fisico')->label('Avanço Físico')
                    ->state(fn (Projeto $record) => $record->avancoFisico().'%')
                    ->badge()
                    ->color(fn (Projeto $record) => match (true) { $record->avancoFisico() >= 100 => 'success', $record->avancoFisico() >= 50 => 'info', $record->avancoFisico() > 0 => 'warning', default => 'gray' }),
                TextColumn::make('avanco_financeiro')->label('Avanço Financeiro')
                    ->state(fn (Projeto $record) => $record->valor_orcamento ? $record->avancoFinanceiro().'%' : '— sem orçamento')
                    ->badge()
                    ->color(fn (Projeto $record) => match (true) { $record->avancoFinanceiro() >= 100 => 'success', $record->avancoFinanceiro() >= 50 => 'info', $record->avancoFinanceiro() > 0 => 'warning', default => 'gray' }),
                TextColumn::make('desvio')->label('Desvio (Físico - Financeiro)')
                    ->state(function (Projeto $record) {
                        if (! $record->valor_orcamento) return '—';
                        $desvio = $record->avancoFisico() - $record->avancoFinanceiro();
                        return ($desvio >= 0 ? '+' : '').number_format($desvio, 2, ',', '.').'%';
                    })
                    ->badge()
                    ->color(function (Projeto $record) {
                        if (! $record->valor_orcamento) return 'gray';
                        $desvio = $record->avancoFisico() - $record->avancoFinanceiro();
                        return $desvio < -10 ? 'danger' : ($desvio < 0 ? 'warning' : 'success');
                    })
                    ->tooltip('Positivo: obra avança mais rápido que o desembolso. Negativo: desembolso maior que o avanço físico — atenção.'),
            ])
            ->paginated(false);
    }
}
