<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                DatePicker::make('data')->label('Data')->required()->native(false)->displayFormat('d/m/Y'),
                Select::make('projeto_id')->label('Empreendimento')->native(false)->searchable()->required()
                    ->options(fn () => Projeto::pluck('nome', 'id'))->reactive()->afterStateUpdated(fn ($set) => $set('fase_obra_id', null)),
                Select::make('prestador_id')->label('Prestador/Empreiteiro')->native(false)->searchable()
                    ->options(Prestador::orderBy('nome')->pluck('nome', 'id')),
                Select::make('fase_obra_id')->label('Fase da Obra')->native(false)->reactive()
                    ->options(fn ($get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->disabled(fn ($get) => ! $get('projeto_id')),
                Select::make('status')->label('Status')->native(false)->default('planejado')->required()
                    ->options(['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso']),
                Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Itens Contratados')->columnSpanFull()->schema([
                Repeater::make('itens')->relationship('itens')->label('')
                    ->schema([
                        Select::make('orcamento_item_id')->label('Item do Orçamento (opcional)')->native(false)->searchable()
                            ->options(fn ($get) => OrcamentoItem::where('fase_obra_id', $get('../../fase_obra_id'))->pluck('descricao', 'id'))
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) return;
                                $oi = OrcamentoItem::find($state);
                                if ($oi) { $set('descricao', $oi->descricao); $set('unidade', $oi->unidade); $set('valor_unitario', $oi->valor_unitario); $set('servico_id', $oi->servico_id); }
                            })->live()->columnSpanFull(),
                        Select::make('servico_id')->label('Serviço (catálogo)')->native(false)->searchable()
                            ->options(fn () => Servico::where('ativo', true)->pluck('nome', 'id'))
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) return;
                                $s = Servico::find($state);
                                if ($s) { $set('descricao', $s->nome); $set('unidade', $s->unidade_padrao); }
                            })->live(),
                        TextInput::make('descricao')->label('Descrição do Serviço')->required(),
                        TextInput::make('unidade')->label('Unidade')->maxLength(10),
                        TextInput::make('quantidade_contratada')->label('Quantidade')->numeric()->required(),
                        TextInput::make('valor_unitario')->label('Valor Unitário')->prefix('R$')->required()
                            ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null),
                    ])->columns(3)->itemLabel(fn (array $state): ?string => $state['descricao'] ?? 'Novo item')->addActionLabel('+ Item')->collapsible(),
            ]),
            Section::make('Valores e Datas')->columnSpanFull()->schema([
                TextInput::make('valor_total')->label('Valor Total (soma dos itens)')->prefix('R$')->disabled()->dehydrated()->default(0)
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.')),
                DatePicker::make('data_inicio')->label('Início Previsto')->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('data_previsao_fim')->label('Fim Previsto')->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('data_conclusao')->label('Data Conclusão')->native(false)->displayFormat('d/m/Y'),
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
        ->recordActions([EditAction::make()->slideOver()->modalWidth('5xl'), DeleteAction::make()])
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
