<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PedidoCompraResource\Pages;
use App\Models\PedidoCompra;
use App\Models\ContaPagar;
use App\Models\Projeto;
use App\Models\FaseObra;
use App\Models\Fornecedor;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
class PedidoCompraResource extends Resource
{
    protected static ?string $model = PedidoCompra::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos de Compra';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?int $navigationSort = 4;
    protected static ?string $slug = 'pedidos-compra';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados do Pedido')->schema([
                Forms\Components\TextInput::make('numero')->label('Número')->default(fn () => PedidoCompra::gerarNumero())->required()->maxLength(20)->disabledOn('edit'),
                Forms\Components\Select::make('fornecedor_id')->label('Fornecedor')->options(Fornecedor::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                Forms\Components\Select::make('status')->label('Status')->native(false)->default('rascunho')
                    ->options(['rascunho' => 'Rascunho', 'aprovado' => 'Aprovado', 'recebido_parcial' => 'Recebido Parcial', 'recebido' => 'Recebido', 'cancelado' => 'Cancelado']),
                Forms\Components\Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable()->reactive()->afterStateUpdated(fn (callable $set) => $set('fase_obra_id', null)),
                Forms\Components\Select::make('fase_obra_id')->label('Fase da Obra')->options(fn (callable $get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->searchable()->native(false)->nullable()->disabled(fn (callable $get) => ! $get('projeto_id')),
                Forms\Components\DatePicker::make('data_pedido')->label('Data do Pedido')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                Forms\Components\DatePicker::make('data_previsao_entrega')->label('Previsão de Entrega')->native(false)->displayFormat('d/m/Y'),
                Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(3),
            Section::make('Itens')->schema([
                TableRepeater::make('itens')->relationship()->label('')
                    ->headers([Header::make('Produto')->width('200px'), Header::make('Descrição')->width('200px'), Header::make('Unid.')->width('80px'), Header::make('Qtd.')->width('100px'), Header::make('Valor Unit. (R$)')->width('140px'), Header::make('Total (R$)')->width('140px')])
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
                                if ($state) { $p = Produto::find($state); if ($p) { $set('descricao', $p->nome); $set('unidade', $p->unidade); $set('valor_unitario', $p->preco_referencia); } }
                            }),
                        Forms\Components\TextInput::make('descricao')->label('Descrição')->required()->maxLength(200),
                        Forms\Components\TextInput::make('unidade')->label('Unid.')->default('UN')->maxLength(10),
                        Forms\Components\TextInput::make('quantidade')->label('Qtd.')->numeric()->step(0.01)->default(1)->required()->reactive(),
                        Forms\Components\TextInput::make('valor_unitario')->label('Valor Unit.')->numeric()->prefix('R$')->step(0.01)->default(0)->required()->reactive(),
                        Forms\Components\TextInput::make('valor_total')->label('Total')->numeric()->prefix('R$')->step(0.01)->disabled()->dehydrated()->default(0),
                    ])
                    ->addActionLabel('+ Adicionar Item')->columnSpanFull()->defaultItems(1)->live()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $itens = $get('itens') ?? [];
                        foreach ($itens as $key => $item) { $itens[$key]['valor_total'] = round((float)($item['quantidade'] ?? 0) * (float)($item['valor_unitario'] ?? 0), 2); }
                        $set('itens', $itens);
                    }),
            ]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('numero')->label('Número')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('fornecedor.nome')->label('Fornecedor')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('projeto.nome')->label('Empreendimento')->placeholder('—'),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'rascunho', 'info' => 'aprovado', 'warning' => 'recebido_parcial', 'success' => 'recebido', 'danger' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'rascunho' => 'Rascunho', 'aprovado' => 'Aprovado', 'recebido_parcial' => 'Recebido Parcial', 'recebido' => 'Recebido', 'cancelado' => 'Cancelado', default => $state }),
            Tables\Columns\TextColumn::make('data_pedido')->label('Data')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('valor_total')->label('Valor Total')->money('BRL')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['rascunho' => 'Rascunho', 'aprovado' => 'Aprovado', 'recebido_parcial' => 'Recebido Parcial', 'recebido' => 'Recebido', 'cancelado' => 'Cancelado']),
            Tables\Filters\SelectFilter::make('fornecedor_id')->label('Fornecedor')->options(Fornecedor::pluck('nome', 'id'))->searchable(),
        ])
        ->actions([
            Tables\Actions\Action::make('gerarTitulo')->label('Gerar Título')->icon('heroicon-o-banknotes')->color('warning')
                ->visible(fn (PedidoCompra $record) => ! ContaPagar::where('pedido_compra_id', $record->id)->exists())
                ->form([Forms\Components\DatePicker::make('data_vencimento')->label('Vencimento')->native(false)->displayFormat('d/m/Y')->default(now()->addDays(30))->required()])
                ->action(function (PedidoCompra $record, array $data) {
                    ContaPagar::create(['descricao' => 'Pedido de Compra '.$record->numero, 'contato_tipo' => 'fornecedor', 'contato_id' => $record->fornecedor_id, 'projeto_id' => $record->projeto_id, 'fase_obra_id' => $record->fase_obra_id, 'pedido_compra_id' => $record->id, 'valor' => $record->valor_total, 'data_vencimento' => $data['data_vencimento'], 'status' => 'aberto']);
                })->successNotificationTitle('Título gerado com sucesso'),
            Tables\Actions\EditAction::make()->slideOver()->modalWidth('5xl'),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('created_at', 'desc')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListPedidosCompra::route('/'), 'create' => Pages\CreatePedidoCompra::route('/create'), 'edit' => Pages\EditPedidoCompra::route('/{record}/edit')];
    }
}
