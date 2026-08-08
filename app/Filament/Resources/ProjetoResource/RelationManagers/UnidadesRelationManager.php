<?php
namespace App\Filament\Resources\ProjetoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\RawJs;
use App\Filament\Imports\UnidadeImporter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class UnidadesRelationManager extends RelationManager
{
    protected static string $relationship = 'unidades';
    protected static ?string $title = 'Unidades';
    protected static ?string $modelLabel = 'Unidade';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('identificacao')->label('Identificação')->required()->maxLength(20),
            TextInput::make('tipo')->label('Tipo')->maxLength(50),
            TextInput::make('tipologia')->label('Tipologia')->maxLength(50),
            TextInput::make('andar')->label('Andar')->maxLength(50),
            TextInput::make('vaga_garagem')->label('Vaga de Garagem')->maxLength(50),
            TextInput::make('valor_avaliado')->label('Valor Avaliado')->prefix('R$')->numeric()->step(0.01),
            TextInput::make('area')->label('Área Privativa (m²)')->numeric()->step(0.01),
            TextInput::make('valor_tabela')->label('Valor de Tabela')->prefix('R$')->default(0)
                ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null),
            Select::make('status')->label('Status')->native(false)->default('disponivel')
                ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado', 'indisponivel' => 'Indisponível']),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('identificacao')
            ->columns([
                TextColumn::make('identificacao')->label('Identificação')->searchable()->sortable()->weight('medium'),
                TextColumn::make('tipo')->label('Tipo')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('andar')->label('Andar')->sortable()->placeholder('—'),
                TextColumn::make('tipologia')->label('Tipologia')->searchable()->sortable()->placeholder('—'),
                TextColumn::make('vaga_garagem')->label('Vaga')->sortable()->placeholder('—'),
                TextColumn::make('area')->label('Área Privativa (m²)')->numeric(decimalPlaces: 2)->sortable()->placeholder('—'),
                TextColumn::make('valor_tabela')->label('Valor de Tabela')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('valor_avaliado')->label('Valor Avaliado')->money('BRL')->alignEnd()->sortable()->placeholder('—'),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['success' => 'disponivel', 'warning' => 'reservado', 'gray' => 'vendido', 'danger' => 'distratado', 'secondary' => 'indisponivel'])
                    ->formatStateUsing(fn ($state) => ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado', 'indisponivel' => 'Indisponível'][$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('status')->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado', 'indisponivel' => 'Indisponível']),
            ])
            ->headerActions([
                CreateAction::make()->label('+ Unidade')->slideOver(),
                Action::make('baixarModeloUnidades')->label('Baixar Modelo')->icon('heroicon-o-arrow-down-tray')->color('info')->url('/download/unidades-template')->openUrlInNewTab(),
                ImportAction::make('importarUnidades')->label('Importar Planilha')->importer(UnidadeImporter::class)
                    ->options(fn () => ['projeto_id' => $this->getOwnerRecord()->id]),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('4xl')->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('identificacao');
    }
}
