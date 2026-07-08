<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PlanoContaResource\Pages;
use App\Models\PlanoConta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class PlanoContaResource extends Resource
{
    protected static ?string $model = PlanoConta::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Plano de Contas';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'plano-contas';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo')->label('Código')->required()->maxLength(20)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(100)->columnSpanFull(),
            Forms\Components\Select::make('tipo')->label('Tipo')->native(false)->default('despesa')->required()->options(['despesa' => 'Despesa', 'receita' => 'Receita']),
            Forms\Components\Select::make('plano_conta_pai_id')->label('Conta Pai')->options(PlanoConta::orderBy('codigo')->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('codigo')->label('Código')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->weight('medium'),
            Tables\Columns\TextColumn::make('pai.nome')->label('Conta Pai')->placeholder('—'),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo')->badge()->colors(['danger' => 'despesa', 'success' => 'receita'])->formatStateUsing(fn ($state) => $state === 'despesa' ? 'Despesa' : 'Receita'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('tipo')->options(['despesa' => 'Despesa', 'receita' => 'Receita'])])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('codigo')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListPlanoContas::route('/'), 'create' => Pages\CreatePlanoConta::route('/create'), 'edit' => Pages\EditPlanoConta::route('/{record}/edit')];
    }
}
