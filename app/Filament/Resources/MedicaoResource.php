<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\MedicaoResource\Pages\ListMedicoes;
use App\Models\Medicao;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MedicaoResource extends Resource
{
    protected static ?string $model = Medicao::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Medições';
    protected static string | \UnitEnum | null $navigationGroup = 'Operacional';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'medicoes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('numero')->label('Nº Medição')->numeric()->disabled(),
            DatePicker::make('data_medicao')->label('Data da Medição')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_inicio_periodo')->label('Período Início')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_fim_periodo')->label('Período Fim')->required()->native(false)->displayFormat('d/m/Y'),
            TextInput::make('valor_total')->label('Valor Medido')->numeric()->prefix('R$')->step(0.01)->required(),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['ordemServico.projeto', 'ordemServico.prestador']))
            ->columns([
                TextColumn::make('ordemServico.numero')->label('OS')->searchable()->sortable()->weight('medium'),
                TextColumn::make('ordemServico.projeto.nome')->label('Empreendimento')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('ordemServico.prestador.nome')->label('Prestador')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('numero')->label('Nº')->sortable()->width('60px'),
                TextColumn::make('data_medicao')->label('Data')->date('d/m/Y')->sortable(),
                TextColumn::make('valor_total')->label('Valor')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['gray' => 'rascunho', 'warning' => 'medida', 'success' => 'aprovada', 'info' => 'faturada', 'gray' => 'paga'])
                    ->formatStateUsing(fn ($s) => ['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'][$s] ?? $s),
            ])
            ->filters([
                SelectFilter::make('status')->options(['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga']),
                SelectFilter::make('ordemServico.projeto_id')->label('Empreendimento')->relationship('ordemServico.projeto', 'nome')->searchable(),
            ])
            ->recordActions([
                Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check')->color('success')->iconButton()
                    ->visible(fn (Medicao $record) => $record->status === 'medida' && ! $record->conta_pagar_id)
                    ->requiresConfirmation()
                    ->action(fn (Medicao $record) => $record->aprovarEGerarContaPagar()),
                EditAction::make()->slideOver()->iconButton(),
                DeleteAction::make()->iconButton()->visible(fn (Medicao $record) => $record->status === 'rascunho'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('data_medicao', 'desc')->dragReorderableColumns()->stickableColumns();
    }

    public static function getPages(): array
    {
        return ['index' => ListMedicoes::route('/')];
    }
}
