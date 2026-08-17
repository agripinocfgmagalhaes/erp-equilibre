<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\MedicaoResource\Pages\ListMedicoes;
use App\Filament\Resources\MedicaoResource\Pages\CreateMedicao;
use App\Filament\Resources\MedicaoResource\Pages\EditMedicao;
use App\Models\Medicao;
use App\Models\MedicaoItem;
use App\Models\OrdenServico;
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
            Select::make('ordem_servico_id')->label('Ordem de Serviço')->native(false)->searchable()->required()->live()
                ->options(fn () => OrdenServico::orderByDesc('numero')->get()->mapWithKeys(fn ($os) => [$os->id => $os->numero.' — '.optional($os->projeto)->nome]))
                ->disabled(fn ($record) => $record !== null)
                ->dehydrated()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) { $set('itens', []); $set('numero', 1); return; }
                    $os = OrdenServico::with('itens')->find($state);
                    if (! $os) return;
                    $rows = [];
                    foreach ($os->itens as $item) {
                        $acumulado = (float) (MedicaoItem::where('ordem_servico_item_id', $item->id)->orderByDesc('id')->value('quantidade_acumulada') ?? 0);
                        $saldo = (float) $item->quantidade_contratada - $acumulado;
                        $rows[] = [
                            'ordem_servico_item_id' => $item->id,
                            'descricao_item' => $item->descricao,
                            'saldo' => number_format($saldo, 2, ',', '.'),
                            'quantidade_periodo' => 0,
                        ];
                    }
                    $set('itens', $rows);
                    $set('numero', ((int) $os->medicoes()->max('numero')) + 1);
                }),
            TextInput::make('numero')->label('Nº Medição')->numeric()->disabled()->dehydrated()->default(1),
            DatePicker::make('data_medicao')->label('Data da Medição')->required()->displayFormat('d/m/Y')->default(now()),
            DatePicker::make('data_inicio_periodo')->label('Período Início')->required()->displayFormat('d/m/Y'),
            DatePicker::make('data_fim_periodo')->label('Período Fim')->required()->displayFormat('d/m/Y'),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Repeater::make('itens')->relationship('itens')->label('Itens Medidos')
                ->schema([
                    Hidden::make('ordem_servico_item_id')->required(),
                    TextInput::make('descricao_item')->label('Item')->disabled()->dehydrated(false),
                    TextInput::make('saldo')->label('Saldo Disponível')->disabled()->dehydrated(false),
                    TextInput::make('quantidade_periodo')->label('Qtd. Executada')->numeric()->required()->default(0),
                ])->columns(3)->addable(false)->deletable(false)->reorderable(false)
                ->itemLabel(fn (array $state) => $state['descricao_item'] ?? null)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ordemServico.projeto', 'ordemServico.prestador']))
            ->columns([
                TextColumn::make('ordemServico.numero')->label('OS')->searchable()->sortable(),
                TextColumn::make('ordemServico.projeto.nome')->label('Empreendimento')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('ordemServico.prestador.nome')->label('Prestador')->searchable()->placeholder('—'),
                TextColumn::make('numero')->label('Med.')->sortable()->width('70px'),
                TextColumn::make('data_medicao')->label('Data')->date('d/m/Y')->sortable(),
                TextColumn::make('valor_total')->label('Valor')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['gray' => 'rascunho', 'warning' => 'medida', 'success' => 'aprovada', 'info' => 'faturada', 'gray' => 'paga'])
                    ->formatStateUsing(fn ($s) => ['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'][$s] ?? $s),
            ])
            ->filters([
                SelectFilter::make('status')->options(['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga']),
                SelectFilter::make('ordem_servico_id')->label('Ordem de Serviço')->relationship('ordemServico', 'numero')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check')->color('success')->iconButton()
                    ->visible(fn ($record) => $record->status === 'medida' && ! $record->conta_pagar_id)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->aprovarEGerarContaPagar()),
                EditAction::make()->slideOver()->modalWidth('full')->iconButton(),
                DeleteAction::make()->iconButton()->visible(fn ($record) => $record->status === 'rascunho'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('data_medicao', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicoes::route('/'),
            'create' => CreateMedicao::route('/create'),
            'edit' => EditMedicao::route('/{record}/edit'),
        ];
    }
}
