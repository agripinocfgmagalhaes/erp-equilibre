<?php
namespace App\Filament\Widgets;
use App\Models\ContaPagar;
use App\Models\ContaBancaria;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Widgets\TableWidget as BaseWidget;
class VencimentosHojeWidget extends BaseWidget
{
    protected static ?string $heading = 'Vencimentos de Hoje';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(ContaPagar::whereDate('data_vencimento', today())->whereIn('status', ['aberto', 'vencido']))
            ->columns([
                Tables\Columns\TextColumn::make('descricao')->label('Descrição')->limit(40),
                Tables\Columns\TextColumn::make('nome_contato')->label('Contato')->getStateUsing(fn ($record) => $record->nome_contato_attribute),
                Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->colors(['gray' => 'aberto', 'danger' => 'vencido']),
            ])
            ->paginated(false);
    }
}
