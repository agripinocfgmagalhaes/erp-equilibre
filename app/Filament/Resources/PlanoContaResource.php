<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PlanoContaResource\Pages\ListPlanoContas;
use App\Filament\Resources\PlanoContaResource\Pages\CreatePlanoConta;
use App\Filament\Resources\PlanoContaResource\Pages\EditPlanoConta;
use App\Filament\Resources\PlanoContaResource\Pages;
use App\Models\PlanoConta;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class PlanoContaResource extends Resource
{
    protected static ?string $model = PlanoConta::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Plano de Contas';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'plano-contas';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')->label('Código')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('nome')->label('Nome')->required()->maxLength(100)->columnSpanFull(),
            Select::make('tipo')->label('Tipo')->native(false)->default('despesa')->required()->options(['despesa' => 'Despesa', 'receita' => 'Receita']),
            Select::make('plano_conta_pai_id')->label('Conta Pai')->options(PlanoConta::orderBy('codigo')->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('codigo')->label('Código')->sortable()->searchable(),
            TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            TextColumn::make('pai.nome')->label('Conta Pai')->placeholder('—'),
            TextColumn::make('tipo')->label('Tipo')->badge()->colors(['danger' => 'despesa', 'success' => 'receita'])->formatStateUsing(fn ($state) => $state === 'despesa' ? 'Despesa' : 'Receita'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([SelectFilter::make('tipo')->options(['despesa' => 'Despesa', 'receita' => 'Receita'])])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('codigo')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListPlanoContas::route('/'), 'create' => CreatePlanoConta::route('/create'), 'edit' => EditPlanoConta::route('/{record}/edit')];
    }
}
