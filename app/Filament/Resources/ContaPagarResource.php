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
use Filament\Tables\Filters\SelectFilter;
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
                Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable()->reactive()->afterStateUpdated(fn (callable $set) => $set('fase_obra_id', null)),
                Select::make('fase_obra_id')->label('Fase da Obra')->options(fn (callable $get) => FaseObra::where('projeto_id', $get('projeto_id'))->pluck('nome', 'id'))->searchable()->native(false)->nullable()->disabled(fn (callable $get) => ! $get('projeto_id')),
                Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Hidden::make('status')->default('aberto'),
            ])->columns(2),
            Section::make('Valores e Datas')->schema([
                TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                DatePicker::make('data_vencimento')->label('Vencimento')->native(false)->displayFormat('d/m/Y')->required(),
                DatePicker::make('data_pagamento')->label('Data de Pagamento')->native(false)->displayFormat('d/m/Y')->readOnly(),
                Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('descricao')->label('Descrição')->searchable()->weight('medium')->limit(40),
            TextColumn::make('nome_contato')->label('Contato')->getStateUsing(fn ($record) => $record->nome_contato_attribute),
            TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
            TextColumn::make('valor_pago')->label('Pago')->money('BRL')->sortable(),
            TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'pago', 'danger' => 'vencido', 'warning' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state }),
        ])
        ->filters([SelectFilter::make('status')->options(['aberto' => 'Aberto', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'])])
        ->recordActions([
            Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (ContaPagar $record) => ! in_array($record->status, ['pago', 'cancelado']))
                ->schema([
                    TextInput::make('valor_pago')->label('Valor Pago')->numeric()->prefix('R$')->step(0.01)->required(),
                    DatePicker::make('data_pagamento')->label('Data do Pagamento')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                    Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaPagar $record) => ['valor_pago' => $record->valor])
                ->action(fn (ContaPagar $record, array $data) => $record->darBaixa((float) $data['valor_pago'], $data['data_pagamento'], $data['conta_bancaria_id'] ?? null)),
            EditAction::make()->slideOver()->modalWidth('4xl'),
            DeleteAction::make(),
        ])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('data_vencimento')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListContasPagar::route('/'), 'create' => CreateContaPagar::route('/create'), 'edit' => EditContaPagar::route('/{record}/edit')];
    }
}
