<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ContaReceberResource\Pages;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\PlanoConta;
use App\Models\ContaBancaria;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaReceberResource extends Resource
{
    protected static ?string $model = ContaReceber::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Contas a Receber';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'contas-receber';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados do Título')->schema([
                Forms\Components\TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('cliente_id')->label('Cliente')->options(Cliente::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Select::make('plano_conta_id')->label('Plano de Conta')->options(PlanoConta::where('tipo', 'receita')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Forms\Components\Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Forms\Components\Hidden::make('status')->default('aberto'),
            ])->columns(2),
            Section::make('Valores e Datas')->schema([
                Forms\Components\TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                Forms\Components\TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                Forms\Components\DatePicker::make('data_vencimento')->label('Vencimento')->native(false)->displayFormat('d/m/Y')->required(),
                Forms\Components\DatePicker::make('data_recebimento')->label('Data de Recebimento')->native(false)->displayFormat('d/m/Y')->readOnly(),
                Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('descricao')->label('Descrição')->searchable()->weight('medium')->limit(40),
            Tables\Columns\TextColumn::make('cliente.nome')->label('Cliente')->searchable()->placeholder('—'),
            Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
            Tables\Columns\TextColumn::make('valor_recebido')->label('Recebido')->money('BRL')->sortable(),
            Tables\Columns\TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'recebido', 'danger' => 'vencido', 'warning' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state }),
        ])
        ->filters([Tables\Filters\SelectFilter::make('status')->options(['aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'])])
        ->actions([
            Tables\Actions\Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (ContaReceber $record) => ! in_array($record->status, ['recebido', 'cancelado']))
                ->form([
                    Forms\Components\TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->required(),
                    Forms\Components\DatePicker::make('data_recebimento')->label('Data do Recebimento')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                    Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaReceber $record) => ['valor_recebido' => $record->valor])
                ->action(fn (ContaReceber $record, array $data) => $record->darBaixa((float) $data['valor_recebido'], $data['data_recebimento'], $data['conta_bancaria_id'] ?? null)),
            Tables\Actions\EditAction::make()->slideOver()->modalWidth('4xl'),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('data_vencimento')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListContasReceber::route('/'), 'create' => Pages\CreateContaReceber::route('/create'), 'edit' => Pages\EditContaReceber::route('/{record}/edit')];
    }
}
