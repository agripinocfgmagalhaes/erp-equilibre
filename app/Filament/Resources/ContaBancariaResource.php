<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContaBancariaResource\Pages\ListContasBancarias;
use App\Filament\Resources\ContaBancariaResource\Pages\CreateContaBancaria;
use App\Filament\Resources\ContaBancariaResource\Pages\EditContaBancaria;
use App\Filament\Resources\ContaBancariaResource\Pages;
use App\Models\ContaBancaria;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaBancariaResource extends Resource
{
    protected static ?string $model = ContaBancaria::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Contas Bancárias';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'contas-bancarias';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nome')->label('Identificação')->required()->maxLength(100)->columnSpanFull()->placeholder('Ex: Conta Principal'),
            Select::make('tipo')->label('Tipo')->native(false)->default('corrente')->required()->options(['corrente' => 'Conta Corrente', 'poupanca' => 'Poupança', 'caixa' => 'Caixa']),
            TextInput::make('banco')->label('Banco')->maxLength(100),
            TextInput::make('agencia')->label('Agência')->maxLength(20),
            TextInput::make('conta')->label('Conta')->maxLength(30),
            TextInput::make('saldo_inicial')->label('Saldo Inicial')->numeric()->prefix('R$')->step(0.01)->default(0),
            Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Identificação')->searchable()->weight('medium'),
            TextColumn::make('banco')->label('Banco')->placeholder('—'),
            TextColumn::make('tipo')->label('Tipo')->badge()->formatStateUsing(fn ($state) => match($state) { 'corrente' => 'Conta Corrente', 'poupanca' => 'Poupança', 'caixa' => 'Caixa', default => $state }),
            TextColumn::make('saldo_inicial')->label('Saldo Inicial')->money('BRL'),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->recordActions([EditAction::make()->slideOver(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListContasBancarias::route('/'), 'create' => CreateContaBancaria::route('/create'), 'edit' => EditContaBancaria::route('/{record}/edit')];
    }
}
