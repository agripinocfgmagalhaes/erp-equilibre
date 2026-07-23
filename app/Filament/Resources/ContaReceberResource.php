<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContaReceberResource\Pages\ListContasReceber;
use App\Filament\Resources\ContaReceberResource\Pages\CreateContaReceber;
use App\Filament\Resources\ContaReceberResource\Pages\EditContaReceber;
use App\Filament\Resources\ContaReceberResource\Pages;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\PlanoConta;
use App\Models\ContaBancaria;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaReceberResource extends Resource
{
    protected static ?string $model = ContaReceber::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Contas a Receber';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'contas-receber';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Título')->schema([
                TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
                Select::make('cliente_id')->label('Cliente')->options(Cliente::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('plano_conta_id')->label('Plano de Conta')->options(PlanoConta::where('tipo', 'receita')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Hidden::make('status')->default('aberto'),
            ])->columns(2),
            Section::make('Valores e Datas')->schema([
                TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                DatePicker::make('data_vencimento')->label('Vencimento')->native(false)->displayFormat('d/m/Y')->required(),
                DatePicker::make('data_recebimento')->label('Data de Recebimento')->native(false)->displayFormat('d/m/Y')->readOnly(),
                Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('descricao')->label('Descrição')->searchable()->weight('medium')->limit(40),
            TextColumn::make('cliente.nome')->label('Cliente')->searchable()->placeholder('—'),
            TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
            TextColumn::make('valor_recebido')->label('Recebido')->money('BRL')->sortable(),
            TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'recebido', 'danger' => 'vencido', 'warning' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state }),
        ])
        ->filters([SelectFilter::make('status')->options(['aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'])])
        ->recordActions([
            Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (ContaReceber $record) => ! in_array($record->status, ['recebido', 'cancelado']))
                ->schema([
                    TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->required(),
                    DatePicker::make('data_recebimento')->label('Data do Recebimento')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
                    Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaReceber $record) => ['valor_recebido' => $record->valor])
                ->action(fn (ContaReceber $record, array $data) => $record->darBaixa((float) $data['valor_recebido'], $data['data_recebimento'], $data['conta_bancaria_id'] ?? null)),
            EditAction::make()->slideOver()->modalWidth('4xl'),
            DeleteAction::make(),
        ])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('data_vencimento')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListContasReceber::route('/'), 'create' => CreateContaReceber::route('/create'), 'edit' => EditContaReceber::route('/{record}/edit')];
    }
}
