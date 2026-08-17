<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Medicao;
use App\Models\MedicaoItem;
use App\Filament\Resources\OrdenServicoResource\Pages\ListOrdenServico;
use App\Filament\Resources\OrdenServicoResource\Pages\CreateOrdenServico;
use App\Filament\Resources\OrdenServicoResource\Pages\EditOrdenServico;
use Filament\Support\RawJs;
use App\Models\OrdenServico;
use App\Models\Prestador;
use App\Models\Projeto;
use App\Models\FaseObra;
use App\Models\Servico;
use App\Models\OrcamentoItem;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\OrdenServicoResource\RelationManagers\MedicoesRelationManager;

class OrdenServicoResource extends Resource
{
    protected static ?string $model = OrdenServico::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Ordens de Serviço';
    protected static string | \UnitEnum | null $navigationGroup = 'Operacional';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'ordens-servico';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados Gerais')->columnSpanFull()->schema([
                TextInput::make('numero')->label('Número da OS')->required()->unique(ignoreRecord: true)->maxLength(20),
                DatePicker::make('data')->label('Data')->required()->displayFormat('d/m/Y'),
                Select::make('projeto_id')->label('Empreendimento')->native(false)->searchable()->required()
                    ->options(fn () => Projeto::pluck('nome', 'id'))->reactive(),
                Select::make('prestador_id')->label('Prestador/Empreiteiro')->native(false)->searchable()
                    ->options(Prestador::orderBy('nome')->pluck('nome', 'id')),
                Select::make('fase_padrao_id')->label('Fase')->native(false)->searchable()
                    ->options(fn () => \App\Models\FasePadrao::orderBy('ordem')->pluck('nome', 'id')),
                Select::make('status')->label('Status')->native(false)->default('planejado')->required()
                    ->options(['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso']),
                Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Itens Contratados')->columnSpanFull()->schema([
                Repeater::make('itens')->relationship('itens')->label('')
                    ->schema([
                        Select::make('item_selecionado')->label('Serviço')->native(false)->searchable()->required()->live()
                            ->options(function ($get) {
                                $itens = OrcamentoItem::where('fase_padrao_id', $get('../../../fase_padrao_id'))->pluck('descricao', 'id');
                                if ($itens->isNotEmpty()) return $itens->mapWithKeys(fn ($desc, $id) => ["oi_{$id}" => $desc]);
                                return Servico::where('ativo', true)->pluck('nome', 'id')->mapWithKeys(fn ($nome, $id) => ["sv_{$id}" => $nome]);
                            })
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->orcamento_item_id) $component->state('oi_'.$record->orcamento_item_id);
                                elseif ($record?->servico_id) $component->state('sv_'.$record->servico_id);
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state || ! str_contains($state, '_')) return;
                                [$tipo, $id] = explode('_', $state, 2);
                                if ($tipo === 'oi') {
                                    $oi = OrcamentoItem::find($id);
                                    if ($oi) { $set('orcamento_item_id', $oi->id); $set('servico_id', $oi->servico_id); $set('descricao', $oi->descricao); $set('unidade', $oi->unidade); $set('valor_unitario', $oi->valor_unitario); }
                                } else {
                                    $s = Servico::find($id);
                                    if ($s) { $set('orcamento_item_id', null); $set('servico_id', $s->id); $set('descricao', $s->nome); $set('unidade', $s->unidade_padrao); }
                                }
                            })
                            ->dehydrated(false)->columnSpan(2),
                        Hidden::make('orcamento_item_id'),
                        Hidden::make('servico_id'),
                        Hidden::make('descricao')->required(),
                        Hidden::make('unidade'),
                        TextInput::make('quantidade_contratada')->label('Quantidade')->numeric()->required()->live(onBlur: true),
                        TextInput::make('valor_unitario')->label('Valor Unitário')->prefix('R$')->required()
                            ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null)
                            ->live(onBlur: true),
                        Placeholder::make('item_total')->label('Total')
                            ->content(function ($get) {
                                $qtd = (float) ($get('quantidade_contratada') ?? 0);
                                $valorStr = (string) ($get('valor_unitario') ?? '0');
                                $valor = (float) str_replace(['.', ','], ['', '.'], $valorStr);
                                return 'R$ '.number_format($qtd * $valor, 2, ',', '.');
                            }),
                    ])->columns(5)->itemLabel(fn (array $state): ?string => $state['descricao'] ?? 'Novo item')->addActionLabel('+ Item')->collapsible(),
            ]),
            Section::make('Valores e Datas')->columnSpanFull()->schema([
                Placeholder::make('valor_total_display')->label('Valor Total (soma dos itens)')
                    ->content(function ($get) {
                        $total = 0;
                        foreach (($get('itens') ?? []) as $item) {
                            $qtd = (float) ($item['quantidade_contratada'] ?? 0);
                            $valor = (float) str_replace(['.', ','], ['', '.'], (string) ($item['valor_unitario'] ?? '0'));
                            $total += $qtd * $valor;
                    }
                    return 'R$ '.number_format($total, 2, ',', '.');
                    }),
                Hidden::make('valor_total')
                    ->dehydrateStateUsing(function ($get) {
                        $total = 0;
                        foreach (($get('itens') ?? []) as $item) {
                            $qtd = (float) ($item['quantidade_contratada'] ?? 0);
                            $valor = (float) str_replace(['.', ','], ['', '.'], (string) ($item['valor_unitario'] ?? '0'));
                        $total += $qtd * $valor;
                    }
                    return $total;
                    }),
                DatePicker::make('data_inicio')->label('Início Previsto')->displayFormat('d/m/Y'),
                DatePicker::make('data_previsao_fim')->label('Fim Previsto')->displayFormat('d/m/Y'),
                DatePicker::make('data_conclusao')->label('Data Conclusão')->displayFormat('d/m/Y'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('numero')->label('OS')->searchable()->sortable()->weight('medium')->width('90px'),
            TextColumn::make('data')->label('Data')->date('d/m/Y')->sortable(),
            TextColumn::make('projeto.nome')->label('Empreendimento')->searchable()->sortable(),
            TextColumn::make('prestador.nome')->label('Prestador')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('valor_total')->label('Contratado')->money('BRL')->alignEnd()->sortable(),
            TextColumn::make('valor_medido')->label('Medido')->alignEnd()
                ->state(fn ($record) => 'R$ '.number_format($record->valorMedido(), 2, ',', '.')),
            TextColumn::make('percentual')->label('%')->alignEnd()
                ->state(fn ($record) => $record->percentualMedido().'%'),
            TextColumn::make('status')->label('Status')->badge()->sortable()
                ->colors(['gray' => 'planejado', 'info' => 'em_execucao', 'success' => 'concluido', 'danger' => 'suspenso'])
                ->formatStateUsing(fn ($s) => ['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso'][$s] ?? $s),
        ])
        ->modifyQueryUsing(fn ($query) => $query->with(['projeto', 'prestador']))
        ->recordActions([
            Action::make('medir')->label('Medir')->icon('heroicon-o-clipboard-document-check')->color('info')->iconButton()
                ->slideOver()->modalWidth('4xl')
                ->fillForm(function ($record) {
                    $rows = [];
                    foreach ($record->itens as $item) {
                        $acumulado = (float) (MedicaoItem::where('ordem_servico_item_id', $item->id)->orderByDesc('id')->value('quantidade_acumulada') ?? 0);
                        $saldo = (float) $item->quantidade_contratada - $acumulado;
                        $rows[] = [
                            'ordem_servico_item_id' => $item->id,
                            'descricao_item' => $item->descricao,
                            'saldo' => number_format($saldo, 2, ',', '.'),
                            'quantidade_periodo' => 0,
                        ];
                    }
                    return [
                        'numero' => ((int) $record->medicoes()->max('numero')) + 1,
                        'data_medicao' => now()->toDateString(),
                        'itens' => $rows,
                    ];
                })
                ->schema([
                    TextInput::make('numero')->label('Nº Medição')->numeric()->disabled()->dehydrated(),
                    DatePicker::make('data_medicao')->label('Data da Medição')->required()->displayFormat('d/m/Y'),
                    DatePicker::make('data_inicio_periodo')->label('Período Início')->required()->displayFormat('d/m/Y'),
                    DatePicker::make('data_fim_periodo')->label('Período Fim')->required()->displayFormat('d/m/Y'),
                    Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
                    Repeater::make('itens')->label('Itens Medidos')
                        ->schema([
                            TextInput::make('ordem_servico_item_id')->hidden()->dehydrated(),
                            TextInput::make('descricao_item')->label('Item')->disabled()->dehydrated(),
                            TextInput::make('saldo')->label('Saldo Disponível')->disabled()->dehydrated(),
                            TextInput::make('quantidade_periodo')->label('Qtd. Executada')->numeric()->required()->default(0),
                        ])->columns(3)->addable(false)->deletable(false)->reorderable(false)
                        ->itemLabel(fn (array $state) => $state['descricao_item'] ?? null)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data, $record) {
                    $medicao = Medicao::create([
                        'ordem_servico_id' => $record->id,
                        'numero' => $data['numero'],
                        'data_medicao' => $data['data_medicao'],
                        'data_inicio_periodo' => $data['data_inicio_periodo'],
                        'data_fim_periodo' => $data['data_fim_periodo'],
                        'observacoes' => $data['observacoes'] ?? null,
                        'valor_total' => 0,
                        'status' => 'rascunho',
                    ]);
                    foreach ($data['itens'] as $row) {
                        MedicaoItem::create([
                            'medicao_id' => $medicao->id,
                            'ordem_servico_item_id' => $row['ordem_servico_item_id'],
                            'quantidade_periodo' => $row['quantidade_periodo'],
                            'quantidade_acumulada' => 0,
                            'valor_total' => 0,
                        ]);
                    }
                    \Filament\Notifications\Notification::make()->title('Medição criada')->success()->send();
                }),
            EditAction::make()->slideOver()->modalWidth('full')->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('numero')->dragReorderableColumns()->stickableColumns();
    }

    public static function getRelations(): array
    {
        return [MedicoesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrdenServico::route('/'),
            'create' => CreateOrdenServico::route('/create'),
            'edit' => EditOrdenServico::route('/{record}/edit'),
        ];
    }
}
