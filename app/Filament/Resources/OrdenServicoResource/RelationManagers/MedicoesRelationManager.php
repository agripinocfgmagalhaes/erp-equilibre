<?php
namespace App\Filament\Resources\OrdenServicoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MedicoesRelationManager extends RelationManager
{
    protected static string $relationship = 'medicoes';
    protected static ?string $title = 'Medições';
    protected static ?string $modelLabel = 'Medição';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('numero')->label('Nº Medição')->numeric()
                ->default(fn ($livewire) => ($livewire->getOwnerRecord()->medicoes()->max('numero') ?? 0) + 1)
                ->disabled()->dehydrated(),
            DatePicker::make('data_medicao')->label('Data da Medição')->required()->displayFormat('d/m/Y'),
            DatePicker::make('data_inicio_periodo')->label('Período Início')->required()->displayFormat('d/m/Y'),
            DatePicker::make('data_fim_periodo')->label('Período Fim')->required()->displayFormat('d/m/Y'),
            TextInput::make('valor_total')->label('Valor Medido (soma dos itens)')->prefix('R$')->disabled()->dehydrated()->default(0)
                ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, ',', '.')),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            Repeater::make('itens')->relationship('itens')->label('Itens Medidos')
                ->schema([
                    Hidden::make('ordem_servico_item_id'),
                    TextInput::make('descricao_item')->label('Item')->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->ordemServicoItem?->descricao ?? '—'),
                    TextInput::make('quantidade_periodo')->label('Qtd. Executada')->numeric()->required(),
                    TextInput::make('quantidade_acumulada')->label('Qtd. Acumulada')->numeric()->disabled()->dehydrated(false),
                ])->columns(3)->addable(false)->deletable(false)->reorderable(false)
                ->itemLabel(fn (array $state, $record) => $record?->ordemServicoItem?->descricao ?? null)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')->label('Med.')->sortable()->width('70px'),
                TextColumn::make('data_medicao')->label('Data Medição')->date('d/m/Y')->sortable(),
                TextColumn::make('data_inicio_periodo')->label('Período')->date('d/m/Y')->formatStateUsing(fn ($record) => $record->data_inicio_periodo->format('d/m/Y').' a '.$record->data_fim_periodo->format('d/m/Y')),
                TextColumn::make('valor_total')->label('Valor')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['gray' => 'rascunho', 'warning' => 'medida', 'success' => 'aprovada', 'info' => 'faturada', 'gray' => 'paga'])
                    ->formatStateUsing(fn ($s) => ['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'][$s] ?? $s),
            ])
            ->filters([SelectFilter::make('status')->options(['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'])])
            ->headerActions([
                CreateAction::make()->label('+ Medição')->slideOver()->modalWidth('4xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->using(function (array $data, string $model) {
                        $os = $this->getOwnerRecord();
                        $medicao = $model::create($data + ['ordem_servico_id' => $os->id]);
                        foreach ($os->itens as $item) {
                            $acumuladoAnterior = \App\Models\MedicaoItem::where('ordem_servico_item_id', $item->id)->orderByDesc('id')->value('quantidade_acumulada') ?? 0;
                            \App\Models\MedicaoItem::create([
                                'medicao_id' => $medicao->id,
                                'ordem_servico_item_id' => $item->id,
                                'quantidade_periodo' => 0,
                                'quantidade_acumulada' => $acumuladoAnterior,
                                'valor_total' => 0,
                            ]);
                        }
                        return $medicao;
                    }),
            ])
            ->recordActions([
                Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check')->color('success')->iconButton()
                    ->visible(fn ($record) => $record->status === 'medida' && ! $record->conta_pagar_id)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->aprovarEGerarContaPagar()),
                EditAction::make()->slideOver()->modalWidth('4xl')->iconButton(),
                DeleteAction::make()->iconButton()->visible(fn ($record) => $record->status === 'rascunho'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('numero');
    }
}
