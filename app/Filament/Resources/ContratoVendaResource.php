<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ContratoVendaResource\Pages;
use App\Models\ContratoVenda;
use App\Models\ContaReceber;
use App\Models\Unidade;
use App\Models\Cliente;
use App\Models\Corretor;
use App\Models\PlanoConta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContratoVendaResource extends Resource
{
    protected static ?string $model = ContratoVenda::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Contratos de Venda';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'contratos-venda';
    protected static function calcularPMT(float $pv, float $taxa, int $n): float
    {
        if ($pv <= 0 || $n <= 0) return 0;
        if ($taxa <= 0) return round($pv / $n, 2);
        $i = $taxa / 100;
        return round($pv * $i / (1 - pow(1 + $i, -$n)), 2);
    }
    protected static function recalcParcelamento(callable $set, callable $get): void
    {
        $parc = round((float)$get('valor_venda') - (float)$get('valor_sinal') - (float)$get('valor_fgts') - (float)$get('valor_subsidio') - (float)$get('valor_financiamento'), 2);
        $set('valor_parcelamento', max(0, $parc));
        $set('valor_parcela', static::calcularPMT(max(0, $parc), (float)$get('taxa_juros') ?: 1.2, (int)$get('qtd_parcelas')));
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados do Contrato')->schema([
                Forms\Components\TextInput::make('numero')->label('Número')->default(fn () => ContratoVenda::gerarNumero())->required()->maxLength(20)->disabledOn('edit'),
                Forms\Components\Select::make('status')->label('Status')->native(false)->default('ativo')->options(['ativo' => 'Ativo', 'distratado' => 'Distratado', 'cancelado' => 'Cancelado']),
                Forms\Components\DatePicker::make('data_contrato')->label('Data do Contrato')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                Forms\Components\DatePicker::make('data_entrega_prevista')->label('Previsão de Entrega')->native(false)->displayFormat('d/m/Y'),
            ])->columns(2),
            Section::make('Partes')->schema([
                Forms\Components\Select::make('unidade_id')->label('Unidade')
                    ->options(function ($record) {
                        return Unidade::where(function ($q) use ($record) { $q->where('status', 'disponivel'); if ($record) $q->orWhere('id', $record->unidade_id); })->with('projeto')->get()
                            ->mapWithKeys(fn ($u) => [$u->id => ($u->projeto->nome ?? '?').' - '.$u->identificacao.' (R$ '.number_format($u->valor_tabela, 2, ',', '.').')']);
                    })->searchable()->native(false)->required()->live()
                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                        if ($state) { $u = Unidade::find($state); if ($u) { $set('valor_venda', $u->valor_tabela); $set('valor_comissao', round($u->valor_tabela * 4.5 / 100, 2)); static::recalcParcelamento($set, $get); } }
                    }),
                Forms\Components\Hidden::make('projeto_id'),
                Forms\Components\Select::make('cliente_id')->label('Cliente')->options(Cliente::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                Forms\Components\Select::make('corretor_id')->label('Corretor')->options(Corretor::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
            ])->columns(2),
            Section::make('Composição do Valor')->schema([
                Forms\Components\TextInput::make('valor_venda')->label('Valor Total de Venda')->numeric()->prefix('R$')->step(0.01)->required()->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, callable $get, $state) { $set('valor_comissao', round((float)$state * 4.5 / 100, 2)); static::recalcParcelamento($set, $get); }),
                Forms\Components\TextInput::make('valor_comissao')->label('Comissão (4,5%)')->numeric()->prefix('R$')->step(0.01)->readOnly(),
                Forms\Components\TextInput::make('valor_sinal')->label('Sinal')->numeric()->prefix('R$')->step(0.01)->default(0)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
                Forms\Components\TextInput::make('valor_fgts')->label('FGTS')->numeric()->prefix('R$')->step(0.01)->default(0)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
                Forms\Components\TextInput::make('valor_subsidio')->label('Subsídio')->numeric()->prefix('R$')->step(0.01)->default(0)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
                Forms\Components\TextInput::make('valor_financiamento')->label('Financiamento Bancário')->numeric()->prefix('R$')->step(0.01)->default(0)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
            ])->columns(3),
            Section::make('Parcelamento Direto')->schema([
                Forms\Components\TextInput::make('valor_parcelamento')->label('Valor Total do Parcelamento')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                Forms\Components\TextInput::make('qtd_parcelas')->label('Nº de Parcelas')->numeric()->default(0)->minValue(0)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
                Forms\Components\TextInput::make('taxa_juros')->label('Taxa de Juros (% a.m.)')->numeric()->suffix('% a.m.')->step(0.001)->default(1.200)->live(onBlur: true)->afterStateUpdated(fn (callable $set, callable $get) => static::recalcParcelamento($set, $get)),
                Forms\Components\TextInput::make('valor_parcela')->label('Valor da Parcela (PMT)')->numeric()->prefix('R$')->step(0.01)->readOnly(),
                Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('numero')->label('Número')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('unidade.identificacao')->label('Unidade')->searchable(),
            Tables\Columns\TextColumn::make('unidade.projeto.nome')->label('Empreendimento')->searchable(),
            Tables\Columns\TextColumn::make('cliente.nome')->label('Cliente')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('valor_venda')->label('Valor')->money('BRL')->sortable(),
            Tables\Columns\TextColumn::make('data_contrato')->label('Data')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(['success' => 'ativo', 'warning' => 'distratado', 'danger' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'ativo' => 'Ativo', 'distratado' => 'Distratado', 'cancelado' => 'Cancelado', default => $state }),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['ativo' => 'Ativo', 'distratado' => 'Distratado', 'cancelado' => 'Cancelado'])])
        ->actions([
            Tables\Actions\Action::make('gerarCR')->label('Gerar CR')->icon('heroicon-o-banknotes')->color('warning')
                ->visible(fn (ContratoVenda $record) => $record->status === 'ativo' && ! ContaReceber::where('contrato_venda_id', $record->id)->exists())
                ->fillForm(fn (ContratoVenda $record) => ['sinal_valor' => $record->valor_sinal, 'parcelas_valor_total' => $record->valor_parcelamento, 'parcelas_quantidade' => $record->qtd_parcelas ?: 1, 'repasse_valor' => $record->valor_repasse])
                ->form([
                    Section::make('Sinal')->schema([
                        Forms\Components\TextInput::make('sinal_valor')->label('Valor do Sinal')->numeric()->prefix('R$')->step(0.01)->default(0),
                        Forms\Components\DatePicker::make('sinal_vencimento')->label('Data')->native(false)->displayFormat('d/m/Y')->default(now()),
                    ])->columns(2),
                    Section::make('Parcelas Diretas')->schema([
                        Forms\Components\TextInput::make('parcelas_valor_total')->label('Valor Total')->numeric()->prefix('R$')->step(0.01)->default(0),
                        Forms\Components\TextInput::make('parcelas_quantidade')->label('Nº de Parcelas')->numeric()->default(1)->minValue(1)->maxValue(360),
                        Forms\Components\DatePicker::make('parcelas_primeiro_venc')->label('1º Vencimento')->native(false)->displayFormat('d/m/Y')->default(now()->addMonth()),
                    ])->columns(3),
                    Section::make('Repasse Bancário (FGTS + Subsídio + Financiamento)')->schema([
                        Forms\Components\TextInput::make('repasse_valor')->label('Valor do Repasse')->numeric()->prefix('R$')->step(0.01)->default(0),
                        Forms\Components\DatePicker::make('repasse_vencimento')->label('Previsão do Repasse')->native(false)->displayFormat('d/m/Y')->default(now()->addMonths(6)),
                    ])->columns(2),
                    Forms\Components\Select::make('plano_conta_id')->label('Plano de Conta (Receita)')->options(PlanoConta::where('tipo', 'receita')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                ])
                ->action(function (ContratoVenda $record, array $data) {
                    $planoConta = $data['plano_conta_id'] ?? null;
                    $projeto = $record->unidade->projeto_id ?? null;
                    if ((float)$data['sinal_valor'] > 0) ContaReceber::create(['descricao' => 'Sinal - '.$record->numero, 'contrato_venda_id' => $record->id, 'cliente_id' => $record->cliente_id, 'projeto_id' => $projeto, 'plano_conta_id' => $planoConta, 'valor' => $data['sinal_valor'], 'data_vencimento' => $data['sinal_vencimento'], 'status' => 'aberto']);
                    $qtd = (int)$data['parcelas_quantidade']; $valorTotal = (float)$data['parcelas_valor_total'];
                    if ($valorTotal > 0 && $qtd > 0) {
                        $taxa = (float)($record->taxa_juros ?: 1.2);
                        $valorParcela = static::calcularPMT($valorTotal, $taxa, $qtd);
                        $dataVenc = \Carbon\Carbon::parse($data['parcelas_primeiro_venc']);
                        for ($i = 1; $i <= $qtd; $i++) ContaReceber::create(['descricao' => 'Parcela '.$i.'/'.$qtd.' - '.$record->numero, 'contrato_venda_id' => $record->id, 'cliente_id' => $record->cliente_id, 'projeto_id' => $projeto, 'plano_conta_id' => $planoConta, 'valor' => $valorParcela, 'data_vencimento' => $dataVenc->copy()->addMonths($i - 1), 'status' => 'aberto']);
                    }
                    if ((float)$data['repasse_valor'] > 0) ContaReceber::create(['descricao' => 'Repasse Bancário - '.$record->numero, 'contrato_venda_id' => $record->id, 'cliente_id' => $record->cliente_id, 'projeto_id' => $projeto, 'plano_conta_id' => $planoConta, 'valor' => $data['repasse_valor'], 'data_vencimento' => $data['repasse_vencimento'], 'status' => 'aberto']);
                })->successNotificationTitle('Recebimentos gerados com sucesso'),
            Tables\Actions\EditAction::make()->slideOver()->modalWidth('4xl'),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('created_at', 'desc')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListContratosVenda::route('/'), 'create' => Pages\CreateContratoVenda::route('/create'), 'edit' => Pages\EditContratoVenda::route('/{record}/edit')];
    }
}
