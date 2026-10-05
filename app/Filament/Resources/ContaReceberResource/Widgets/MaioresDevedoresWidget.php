<?php
namespace App\Filament\Resources\ContaReceberResource\Widgets;
use App\Models\Cliente;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
class MaioresDevedoresWidget extends BaseWidget
{
    protected static ?string $heading = 'Maiores Devedores';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cliente::query()
                    ->withSum(['contasReceber as total_vencido' => fn ($q) => $q->where('status', 'vencido')], 'valor')
                    ->having('total_vencido', '>', 0)
                    ->orderByDesc('total_vencido')
            )
            ->columns([
                TextColumn::make('nome')->label('Cliente'),
                TextColumn::make('total_vencido')->label('Total Vencido')->money('BRL')->color('danger')->sortable(),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }
}
