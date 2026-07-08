<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ContaPagarResource\Pages;
use App\Models\ContaPagar;
use App\Models\Contato;
use App\Models\PlanoConta;
use App\Models\ContaBancaria;
use App\Models\Projeto;
use App\Models\FaseObra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaPagarResource extends Resource
{
    protected static ?string $model = ContaPagar::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationLabel = 'Contas a Pagar';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'contas-pagar';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados do Título')->schema([
                Forms\Components\TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('contato_key')->label('Contato')->options(fn () => Contato::optionsParaSelect())->searchable()->native(false)->nullable()->columnSpanFull()
                    ->afterStateHydrated(function (Forms\Components\Select $component, $record) { if ($record?->contato_tipo && $record?->contato_id) $component->state($record->contato_tipo.'|'.$record->contato_id); })
                    ->dehydrated(false)->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state && str_contains($state, '|')) { [$tipo, $id] = explode('|', $state, 2); $set('contato_tipo', $tipo); $set('contato_id', (int) $id); } else { $set('contato_tipo', null); $set('contato_id', null); }
                    }),
                Forms\Components\Hidden::make('contato_tipo'),
                Forms\Components\Hidden::make('contato_id'),
                Forms\Components\Select::make('plano_conta_id')->label('Plano de Conta')->options(PlanoConta::where('tipo', 'despesa')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable()->reactive()->afterStateUpdated(fn (callable $set) => $set('fase_obra_id', null)),
                Forms\Components\Select::make('fase_obra_id')->label('Fase da Obra')->options(fn (callable $get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->searchable()->native(false)->nullable()->disabled(fn (callable $get) => ! $get('projeto_id')),
                Forms\Components\Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Forms\Components\Hidden::make('status')->default('aberto'),
            ])->columns(2),
            Section::make('Valores e Datas')->schema([
                Forms\Components\TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                Forms\Components\TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                Forms\Components\DatePicker::make('data_vencimento')->label('Vencimento')->native(false)->displayFormat('d/m/Y')->required(),
                Forms\Components\DatePicker::make('data_pagamento')->label('Data de Pagamento')->native(false)->displayFormat('d/m/Y')->readOnly(),
                Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('descricao')->label('Descrição')->searchable()->weight('medium')->limit(40),
            Tables\Columns\TextColumn::make('nome_contato')->label('Contato')->getStateUsing(fn ($record) => $record->nome_contato_attribute),
            Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
            Tables\Columns\TextColumn::make('valor_pago')->label('Pago')->money('BRL')->sortable(),
            Tables\Columns\TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'pago', 'danger' => 'vencido', 'warning' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state }),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'])])
        ->actions([
            Tables\Actions\Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (ContaPagar $record) => ! in_array($record->status, ['pago', 'cancelado']))
                ->form([
                    Forms\Components\TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->required(),
                    Forms\Components\DatePicker::make('data_pagamento')->label('Data do Pagamento')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                    Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaPagar $record) => ['valor_pago' => $record->valor])
                ->action(fn (ContaPagar $record, array $data) => $record->darBaixa((float) $data['valor_pago'], $data['data_pagamento'], $data['conta_bancaria_id'] ?? null)),
            Tables\Actions\EditAction::make()->slideOver()->modalWidth('4xl'),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('data_vencimento')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListContasPagar::route('/'), 'create' => Pages\CreateContaPagar::route('/create'), 'edit' => Pages\EditContaPagar::route('/{record}/edit')];
    }
}
