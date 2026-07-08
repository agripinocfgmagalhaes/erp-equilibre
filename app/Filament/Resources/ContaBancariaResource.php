<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ContaBancariaResource\Pages;
use App\Models\ContaBancaria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaBancariaResource extends Resource
{
    protected static ?string $model = ContaBancaria::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Contas Bancárias';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'contas-bancarias';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->label('Identificação')->required()->maxLength(100)->columnSpanFull()->placeholder('Ex: Conta Principal'),
            Forms\Components\Select::make('tipo')->label('Tipo')->native(false)->default('corrente')->required()->options(['corrente' => 'Conta Corrente', 'poupanca' => 'Poupança', 'caixa' => 'Caixa']),
            Forms\Components\TextInput::make('banco')->label('Banco')->maxLength(100),
            Forms\Components\TextInput::make('agencia')->label('Agência')->maxLength(20),
            Forms\Components\TextInput::make('conta')->label('Conta')->maxLength(30),
            Forms\Components\TextInput::make('saldo_inicial')->label('Saldo Inicial')->numeric()->prefix('R$')->step(0.01)->default(0),
            Forms\Components\Toggle::make('ativo')->label('Ativo')->default(true),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Identificação')->searchable()->weight('medium'),
            Tables\Columns\TextColumn::make('banco')->label('Banco')->placeholder('—'),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo')->badge()->formatStateUsing(fn ($state) => match($state) { 'corrente' => 'Conta Corrente', 'poupanca' => 'Poupança', 'caixa' => 'Caixa', default => $state }),
            Tables\Columns\TextColumn::make('saldo_inicial')->label('Saldo Inicial')->money('BRL'),
            Tables\Columns\IconColumn::make('ativo')->label('Ativo')->boolean(),
        ])
        ->actions([Tables\Actions\EditAction::make()->slideOver(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListContasBancarias::route('/'), 'create' => Pages\CreateContaBancaria::route('/create'), 'edit' => Pages\EditContaBancaria::route('/{record}/edit')];
    }
}
