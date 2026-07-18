<?php
namespace App\Filament\Resources;
use App\Filament\Resources\RequisicaoCompraResource\Pages;
use App\Filament\Resources\RequisicaoCompraResource\RelationManagers\CotacoesRelationManager;
use App\Models\RequisicaoCompra;
use App\Models\Projeto;
use App\Models\FaseObra;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;

class RequisicaoCompraResource extends Resource
{
    protected static ?string $model = RequisicaoCompra::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Requisições de Compra';
    protected static ?string $navigationGroup = 'Compras';
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados da Requisição')->schema([
                Forms\Components\TextInput::make('numero')->label('Número')->default(fn () => RequisicaoCompra::gerarNumero())->required()->maxLength(20)->disabledOn('edit'),
                Forms\Components\Hidden::make('solicitante_id')->default(fn () => Auth::id()),
                Forms\Components\Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable()->reactive()->afterStateUpdated(fn (callable $set) => $set('fase_obra_id', null)),
                Forms\Components\Select::make('fase_obra_id')->label('Fase da Obra')->options(fn (callable $get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->searchable()->native(false)->nullable()->disabled(fn (callable $get) => ! $get('projeto_id')),
                Forms\Components\DatePicker::make('data_requisicao')->label('Data')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                Forms\Components\Textarea::make('justificativa')->label('Justificativa')->rows(2)->columnSpanFull(),
            ])->columns(3)->disabled(fn (?RequisicaoCompra $record) => $record && $record->status !== 'rascunho'),
            Section::make('Itens Solicitados')->schema([
                TableRepeater::make('itens')->relationship()->label('')
                    ->headers([Header::make('Produto')->width('220px'), Header::make('Descrição')->width('220px'), Header::make('Unid.')->width('90px'), Header::make('Qtd.')->width('100px')])
                    ->schema([
                        Forms\Components\Select::make('produto_id')->label('Produto')
                            ->options(Produto::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150),
                                Forms\Components\TextInput::make('unidade')->label('Unid.')->default('UN')->maxLength(10),
                                Forms\Components\TextInput::make('categoria')->label('Categoria')->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data) => Produto::create($data)->getKey())
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) { $p = Produto::find($state); if ($p) { $set('descricao', $p->nome); $set('unidade', $p->unidade); } }
                            }),
                        Forms\Components\TextInput::make('descricao')->label('Descrição')->required()->maxLength(200),
                        Forms\Components\TextInput::make('unidade')->label('Unid.')->default('UN')->maxLength(10),
                        Forms\Components\TextInput::make('quantidade')->label('Qtd.')->numeric()->step(0.01)->default(1)->required(),
                    ])
                    ->addActionLabel('+ Adicionar Item')->columnSpanFull()->defaultItems(1),
            ])->disabled(fn (?RequisicaoCompra $record) => $record && $record->status !== 'rascunho'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('numero')->label('Número')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('projeto.nome')->label('Empreendimento')->placeholder('—'),
            Tables\Columns\TextColumn::make('solicitante.name')->label('Encarregado')->searchable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(self::STATUS_COLORS)
                ->formatStateUsing(fn ($state) => self::STATUS_LABELS[$state] ?? $state),
            Tables\Columns\TextColumn::make('cotacoes_count')->label('Cotações')->counts('cotacoes')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('data_requisicao')->label('Data')->date('d/m/Y')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(self::STATUS_LABELS),
            Tables\Filters\SelectFilter::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable(),
        ])
        ->actions([
            Tables\Actions\Action::make('enviarAprovacao')->label('Enviar p/ Aprovação')->icon('heroicon-o-paper-airplane')->color('info')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho' && $record->itens()->count() > 0)
                ->requiresConfirmation()
                ->action(fn (RequisicaoCompra $record) => $record->enviarParaAprovacao())
                ->successNotificationTitle('Requisição enviada para aprovação'),

            Tables\Actions\Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'pendente_aprovacao' && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->requiresConfirmation()
                ->action(fn (RequisicaoCompra $record) => $record->aprovar(Auth::user()))
                ->successNotificationTitle('Requisição aprovada — liberada para cotação'),

            Tables\Actions\Action::make('reprovar')->label('Reprovar')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (RequisicaoCompra $record) => $record->status === 'pendente_aprovacao' && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->form([Forms\Components\Textarea::make('motivo')->label('Motivo da Reprovação')->required()])
                ->action(fn (RequisicaoCompra $record, array $data) => $record->reprovar(Auth::user(), $data['motivo']))
                ->successNotificationTitle('Requisição reprovada'),

            Tables\Actions\Action::make('compararCotacoes')->label('Comparar e Gerar Pedido')->icon('heroicon-o-scale')->color('warning')
                ->visible(fn (RequisicaoCompra $record) => in_array($record->status, ['em_cotacao', 'cotada']) && $record->cotacoes()->count() > 0 && Auth::user()->hasAnyRole(['responsavel', 'admin']))
                ->form(fn (RequisicaoCompra $record) => [
                    Forms\Components\Radio::make('cotacao_id')->label('Escolha a cotação vencedora')
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

            Tables\Actions\EditAction::make()->slideOver()->modalWidth('6xl')->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho'),
            Tables\Actions\ViewAction::make()->label('Cotações')->icon('heroicon-o-eye')->visible(fn (RequisicaoCompra $record) => $record->status !== 'rascunho'),
            Tables\Actions\DeleteAction::make()->visible(fn (RequisicaoCompra $record) => $record->status === 'rascunho'),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('created_at', 'desc')->striped();
    }

    public static function getRelations(): array
    {
        return [CotacoesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequisicoesCompra::route('/'),
            'create' => Pages\CreateRequisicaoCompra::route('/create'),
            'edit' => Pages\EditRequisicaoCompra::route('/{record}/edit'),
            'view' => Pages\ViewRequisicaoCompra::route('/{record}'),
        ];
    }
}
