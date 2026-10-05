<?php
namespace App\Filament\Resources;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Filament\Resources\FolhaFechamentoResource\Pages\ListFolhaFechamentos;
use App\Models\FolhaFechamento;
use App\Models\Funcionario;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class FolhaFechamentoResource extends Resource
{
    protected static ?string $model = FolhaFechamento::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Fechamentos de Folha';
    protected static ?string $modelLabel = 'Fechamento de Folha';
    protected static string | \UnitEnum | null $navigationGroup = 'Pessoal';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'folha-fechamentos';

    public static function canCreate(): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['funcionario.projeto', 'contaPagar']))
            ->columns([
                TextColumn::make('funcionario.nome')->label('Funcionário')->searchable()->sortable(),
                TextColumn::make('funcionario.projeto.nome')->label('Obra')->placeholder('—'),
                TextColumn::make('periodo_inicio')->label('Período')->date('d/m/Y')
                    ->formatStateUsing(fn ($record) => $record->periodo_inicio->format('d/m').' a '.$record->periodo_fim->format('d/m/Y')),
                TextColumn::make('dias_trabalhados')->label('Dias')->alignCenter(),
                TextColumn::make('valor_diaria')->label('Diária')->money('BRL'),
                TextColumn::make('valor_total')->label('Total')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('contaPagar.status')->label('Pagamento')->badge()
                    ->colors(['warning' => 'aberto', 'danger' => 'vencido', 'success' => 'pago'])
                    ->formatStateUsing(fn ($s) => ['aberto' => 'Aberto', 'vencido' => 'Vencido', 'pago' => 'Pago'][$s] ?? $s)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('funcionario_id')->label('Funcionário')->relationship('funcionario', 'nome')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('verContaPagar')->label('Ver CP')->icon('heroicon-o-arrow-top-right-on-square')->iconButton()
                    ->url(fn ($record) => $record->conta_pagar_id ? ContaPagarResource::getUrl('edit', ['record' => $record->conta_pagar_id]) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => (bool) $record->conta_pagar_id),
            ])
            ->defaultSort('periodo_inicio', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFolhaFechamentos::route('/'),
        ];
    }
}
