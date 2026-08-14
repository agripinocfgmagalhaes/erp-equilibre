<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContaPagarResource\Pages\ListContasPagar;
use App\Filament\Resources\ContaPagarResource\Pages\CreateContaPagar;
use App\Filament\Resources\ContaPagarResource\Pages\EditContaPagar;
use App\Filament\Resources\ContaPagarResource\Pages;
use App\Models\ContaPagar;
use App\Models\Contato;
use App\Models\PlanoConta;
use App\Models\ContaBancaria;
use App\Models\Projeto;
use App\Models\FaseObra;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaPagarResource extends Resource
{
    protected static ?string $model = ContaPagar::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationLabel = 'Contas a Pagar';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'contas-pagar';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Título')->schema([
                TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
                TextInput::make('numero_documento')->label('Nº Documento')->maxLength(50),
                Select::make('contato_key')->label('Contato')->options(fn () => Contato::optionsParaSelect())->searchable()->native(false)->nullable()->columnSpanFull()
                    ->afterStateHydrated(function (Select $component, $record) { if ($record?->contato_tipo && $record?->contato_id) $component->state($record->contato_tipo.'|'.$record->contato_id); })
                    ->dehydrated(false)->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state && str_contains($state, '|')) { [$tipo, $id] = explode('|', $state, 2); $set('contato_tipo', $tipo); $set('contato_id', (int) $id); } else { $set('contato_tipo', null); $set('contato_id', null); }
                    }),
                Hidden::make('contato_tipo'),
                Hidden::make('contato_id'),
                Select::make('plano_conta_id')->label('Plano de Conta')->options(PlanoConta::where('tipo', 'despesa')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('fase_padrao_id')->label('Fase')->options(fn () => \App\Models\FasePadrao::orderBy('ordem')->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Hidden::make('status')->default('aberto'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Valores e Datas')->schema([
                TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                DatePicker::make('data_vencimento')->label('Vencimento')->displayFormat('d/m/Y')->required(),
                DatePicker::make('data_pagamento')->label('Data de Pagamento')->displayFormat('d/m/Y')->readOnly(),
                Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
    public static function table(Table $table): Table
    {
        /** @var \Hydrat\TableLayoutToggle\Concerns\HasToggleableTable $livewire */
        $livewire = $table->getLivewire();
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['projeto', 'planoConta', 'contaBancaria', 'fasePadrao']))
            ->columns($livewire->isGridLayout() ? static::getGridTableColumns() : static::getListTableColumns())
            ->contentGrid(fn () => $livewire->isListLayout() ? null : ['md' => 2, 'lg' => 3])
            ->filters([
                SelectFilter::make('status')->options(['aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado']),
                SelectFilter::make('projeto_id')->label('Empreendimento')->relationship('projeto', 'nome')->searchable()->preload(),
                SelectFilter::make('plano_conta_id')->label('Plano de Conta')->relationship('planoConta', 'nome')->searchable()->preload(),
                SelectFilter::make('conta_bancaria_id')->label('Conta Bancária')->relationship('contaBancaria', 'nome')->searchable()->preload(),
                Filter::make('data_vencimento')
                    ->form([
                        DatePicker::make('de')->label('Vencimento de'),
                        DatePicker::make('ate')->label('Vencimento até'),
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $query
                        ->when($data['de'] ?? null, fn ($q, $date) => $q->whereDate('data_vencimento', '>=', $date))
                        ->when($data['ate'] ?? null, fn ($q, $date) => $q->whereDate('data_vencimento', '<=', $date))
                    ),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
            Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')->iconButton()
                ->visible(fn (ContaPagar $record) => ! in_array($record->status, ['pago', 'cancelado']))
                ->schema([
                    TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->required(),
                    DatePicker::make('data_pagamento')->label('Data do Pagamento')->displayFormat('d/m/Y')->default(now())->required(),
                    Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaPagar $record) => ['valor_pago' => $record->valor])
                ->action(fn (ContaPagar $record, array $data) => $record->darBaixa((float) $data['valor_pago'], $data['data_pagamento'], $data['conta_bancaria_id'] ?? null)),
            Action::make('pagarPix')->label('Pagar via Pix')->icon('heroicon-o-bolt')->color('warning')->iconButton()
                ->visible(fn (ContaPagar $record) => ! in_array($record->status, ['pago', 'cancelado']))
                ->schema([
                    TextInput::make('chave_pix_destino')->label('Chave Pix')->required(),
                    Select::make('tipo_chave_pix_destino')->label('Tipo')->options(['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'telefone' => 'Telefone', 'email' => 'E-mail', 'aleatoria' => 'Aleatória'])->required(),
                    TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                ])
                ->fillForm(function (ContaPagar $record) {
                    $resolvido = app(\App\Services\InterPixPagamentoService::class)->resolverChaveContato($record);
                    return ['chave_pix_destino' => $resolvido['chave'], 'tipo_chave_pix_destino' => $resolvido['tipo'], 'valor' => $record->valor];
                })
                ->action(function (ContaPagar $record, array $data) {
                    try {
                        app(\App\Services\InterPixPagamentoService::class)->enviar($record, $data['chave_pix_destino'], $data['tipo_chave_pix_destino'], (float) $data['valor']);
                        \Filament\Notifications\Notification::make()->title('Pix enviado')->success()->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()->title('Falha ao enviar Pix')->body($e->getMessage())->danger()->send();
                    }
                }),
            EditAction::make()->slideOver()->modalWidth('4xl')->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        ->toolbarActions([BulkActionGroup::make([
            DeleteBulkAction::make(),
            \Filament\Actions\BulkAction::make('pagarPixLote')
                ->label('Pagar via Pix (lote)')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (\Illuminate\Support\Collection $records) {
                    $service = app(\App\Services\InterPixPagamentoService::class);
                    $ok = 0; $falha = 0; $semChave = 0;
                    foreach ($records as $conta) {
                        if (in_array($conta->status, ['pago', 'cancelado'])) continue;
                        $r = $service->resolverChaveContato($conta);
                        if (!$r['chave']) { $semChave++; continue; }
                        try {
                            $service->enviar($conta, $r['chave'], $r['tipo']);
                            $ok++;
                        } catch (\Throwable $e) {
                            $falha++;
                        }
                    }
                    \Filament\Notifications\Notification::make()
                        ->title("Pix: {$ok} enviados, {$falha} falharam, {$semChave} sem chave cadastrada")
                        ->send();
                }),
        ])])
        ->defaultSort('data_vencimento')->dragReorderableColumns()->stickableColumns();
    }
    protected static function getListTableColumns(): array
    {
        return [
            TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            TextColumn::make('numero_documento')->sortable()->label('Nº Doc.')->searchable()->placeholder('—'),
            TextColumn::make('projeto.nome')->sortable()->label('Projeto')->searchable()->placeholder('—')->badge()->color(fn ($record) => $record->projeto->cor ?? 'gray'),
            TextColumn::make('planoConta.nome')->sortable()->label('Plano de Conta')->searchable()->placeholder('—'),
            TextColumn::make('nome_contato')->sortable()->label('Contato')->placeholder('—'),
            TextColumn::make('descricao')->label('Descrição')->searchable()->sortable()->weight('medium'),
            TextColumn::make('fasePadrao.nome')->sortable()->label('Fase')->searchable()->placeholder('—'),
            TextColumn::make('valor')->label('Valor')->money('BRL')->alignEnd()->sortable(),
            TextColumn::make('contaBancaria.nome')->sortable()->label('Conta Bancária')->searchable()->placeholder('—'),
            TextColumn::make('status')->sortable()->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'pago', 'danger' => 'vencido', 'secondary' => 'cancelado'])
                ->formatStateUsing(fn ($state) => ['aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'][$state] ?? $state),
        ];
    }
    protected static function getGridTableColumns(): array
    {
        return [
            Stack::make([
                Split::make([
                    TextColumn::make('descricao')->label('Descrição')->searchable()->sortable()->weight('medium'),
                    TextColumn::make('status')->sortable()->label('Status')->badge()
                        ->colors(['gray' => 'aberto', 'success' => 'pago', 'danger' => 'vencido', 'secondary' => 'cancelado'])
                        ->formatStateUsing(fn ($state) => ['aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'][$state] ?? $state)
                        ->grow(false),
                ]),
                TextColumn::make('nome_contato')->sortable()->label('Contato')->description('Contato', position: 'above')->placeholder('—'),
                Split::make([
                    TextColumn::make('valor')->sortable()->label('Valor')->description('Valor', position: 'above')->money('BRL')->alignEnd(),
                    TextColumn::make('data_vencimento')->sortable()->label('Vencimento')->description('Vencimento', position: 'above')->date('d/m/Y'),
                ]),
            ])->space(3)->extraAttributes(['class' => 'pb-2']),
        ];
    }
    public static function getPages(): array
    {
        return ['index' => ListContasPagar::route('/')];
    }
}
