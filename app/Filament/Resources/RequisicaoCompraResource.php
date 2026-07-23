<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\RequisicaoCompraResource\Pages\ListRequisicoesCompra;
use App\Filament\Resources\RequisicaoCompraResource\Pages\CreateRequisicaoCompra;
use App\Filament\Resources\RequisicaoCompraResource\Pages\EditRequisicaoCompra;
use App\Filament\Resources\RequisicaoCompraResource\Pages\ViewRequisicaoCompra;
use App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource\RelationManagers\CotacoesRelationManager;
use App\Models\RequisicaoCompra;
use App\Models\Projeto;
use App\Models\FaseObra;
use App\Models\Produto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;

class RequisicaoCompraResource extends Resource
{
    protected static ?string $model = RequisicaoCompra::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Requisições de Compra';
    protected static string | \UnitEnum | null $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'requisicoes-compra';

    public const STATUS_LABELS = [
        'rascunho' => 'Rascunho',
        'pendente_aprovacao' => 'Pendente de Aprovação',
        'reprovada' => 'Reprovada',
        'em_cotacao' => 'Em Cotação',
        'cotada' => 'Cotada',
        'pedido_gerado' => 'Pedido Gerado',
        'cancelada' => 'Cancelada',
    ];

    public const STATUS_COLORS = [
        'gray' => ['rascunho', 'cancelada'],
        'warning' => 'pendente_aprovacao',
        'danger' => 'reprovada',
        'info' => 'em_cotacao',
        'primary' => 'cotada',
        'success' => 'pedido_gerado',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da Requisição')->schema([
                TextInput::make('numero')->label('Número')->default(fn () => RequisicaoCompra::gerarNumero())->required()->maxLength(20)->disabledOn('edit'),
                Hidden::make('solicitante_id')->default(fn () => Auth::id()),
                Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable()->reactive()->afterStateUpdated(fn (callable $set) => $set('fase_obra_id', null)),
                Select::make('fase_obra_id')->label('Fase da Obra')->options(fn (callable $get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->searchable()->native(false)->nullable()->disabled(fn (callable $get) => ! $get('projeto_id')),
                DatePicker::make('data_requisicao')->label('Data')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                Textarea::make('justificativa')->label('Justificativa')->rows(2)->columnSpanFull(),
            ])->columns(3)->disabled(fn (?RequisicaoCompra $record) => $record && $record->status !== 'rascunho'),
            Section::make('Itens Solicitados')->schema([
                TableRepeater::make('itens')->relationship()->label('')
                    ->headers([Header::make('Produto')->width('220px'), Header::make('Descrição')->width('220px'), Header::make('Unid.')->width('90px'), Header::make('Qtd.')->width('100px')])
                    ->schema([
                        Select::make('produto_id')->label('Produto')
                            ->options(Produto::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable()
                            ->createOptionForm([
                                TextInput::make('nome')->label('Nome')->required()->maxLength(150),
                                TextInput::make('unidade')->label('Unid.')->default('UN')->maxLength(10),
                                TextInput::make('categoria')->label('Categoria')->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data) => Produto::create($data)->getKey())
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) { $p = Produto::find($state); if ($p) { $set('descricao', $p->nome); $set('unidade', $p->unidade); } }
                            }),
                        TextInput::make('descricao')->label('Descrição')->required()->maxLength(200),
                        TextInput::make('unidade')->label('Unid.')->default('UN')->maxLength(10),
                        TextInput::make('quantidade')->label('Qtd.')->numeric()->step(0.01)->default(1)->required(),
                    ])
                    ->addActionLabel('+ Adicionar Item')->columnSpanFull()->defaultItems(1),
            ])->disabled(fn (?RequisicaoCompra $record) => $record && $record->status !== 'rascunho'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('numero')->label('Número')->searchable()->sortable()->weight('medium'),
            TextColumn::make('projeto.nome')->label('Empreendimento')->placeholder('—'),
            TextColumn::make('solicitante.name')->label('Encarregado')->searchable(),
            TextColumn::make('status')->label('Status')->badge()
                ->colors(self::STATUS_COLORS)
                ->formatStateUsing(fn ($state) => self::STATUS_LABELS[$state] ?? $state),
            TextColumn::make('cotacoes_count')->label('Cotações')->counts('cotacoes')->badge()->color('gray'),
            TextColumn::make('data_requisicao')->label('Data')->date('d/m/Y')->sortable(),
        ])
        ->filters([
            SelectFilter::make('status')->options(self::STATUS_LABELS),
            SelectFilter::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable(),
        ])
        ->recordActions([
            Action::make('enviarAprovacao')->label('Enviar p/ Aprovação')->icon('heroicon-o-paper-airplane')->color('info')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho' && $record->itens()->count() > 0)
                ->requiresConfirmation()
                ->action(fn (RequisicaoCompra $record) => $record->enviarParaAprovacao())
                ->successNotificationTitle('Requisição enviada para aprovação'),

            Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'pendente_aprovacao' && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->requiresConfirmation()
                ->action(fn (RequisicaoCompra $record) => $record->aprovar(Auth::user()))
                ->successNotificationTitle('Requisição aprovada — liberada para cotação'),

            Action::make('reprovar')->label('Reprovar')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'pendente_aprovacao' && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->schema([Textarea::make('motivo')->label('Motivo da Reprovação')->required()])
                ->action(fn (RequisicaoCompra $record, array $data) => $record->reprovar(Auth::user(), $data['motivo']))
                ->successNotificationTitle('Requisição reprovada'),

            Action::make('compararCotacoes')->label('Comparar e Gerar Pedido')->icon('heroicon-o-scale')->color('warning')
                ->visible(fn (RequisicaoCompra $record) => in_array($record->status, ['em_cotacao', 'cotada']) && $record->cotacoes()->count() > 0 && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->schema(fn (RequisicaoCompra $record) => [
                    Radio::make('cotacao_id')->label('Escolha a cotação vencedora')
                        ->options($record->cotacoes()->with('fornecedor')->get()->mapWithKeys(fn ($c) => [
                            $c->id => sprintf('%s — R$ %s — prazo %s dia(s) — %s',
                                $c->fornecedor->nome,
                                number_format($c->valor_total, 2, ',', '.'),
                                $c->prazo_entrega_dias ?? '—',
                                $c->condicao_pagamento ?? 'sem condição informada'
                            ),
                        ]))
                        ->required(),
                ])
                ->action(function (RequisicaoCompra $record, array $data) {
                    $cotacao = $record->cotacoes()->findOrFail($data['cotacao_id']);
                    $record->selecionarVencedoraEGerarPedido($cotacao);
                })
                ->successNotificationTitle('Pedido de compra gerado a partir da cotação vencedora'),

            EditAction::make()->slideOver()->modalWidth('6xl')->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho'),
            ViewAction::make()->label('Cotações')->icon('heroicon-o-eye')->visible(fn (RequisicaoCompra $record) => $record->status !== 'rascunho'),
            DeleteAction::make()->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho'),
        ])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('created_at', 'desc')->striped();
    }

    public static function getRelations(): array
    {
        return [CotacoesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRequisicoesCompra::route('/'),
            'create' => CreateRequisicaoCompra::route('/create'),
            'edit' => EditRequisicaoCompra::route('/{record}/edit'),
            'view' => ViewRequisicaoCompra::route('/{record}'),
        ];
    }
}
