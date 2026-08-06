<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ImportAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ProjetoResource\Pages\ListProjetos;
use App\Filament\Resources\ProjetoResource\Pages\CreateProjeto;
use App\Filament\Resources\ProjetoResource\Pages\EditProjeto;
use Filament\Support\RawJs;
use App\Filament\Imports\ProjetoImporter;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjetoResource\RelationManagers\UnidadesRelationManager;
use App\Filament\Resources\ProjetoResource\RelationManagers\FasesObraRelationManager;
use App\Filament\Resources\ProjetoResource\RelationManagers\OrcamentoItensRelationManager;

class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Empreendimentos';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'projetos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Empreendimento')->schema([
                TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
                Select::make('status')->label('Status')->native(false)->default('planejamento')
                    ->options(['planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado']),
                Select::make('cor')->label('Cor')->native(false)->default('gray')
                    ->options(['gray' => 'Cinza', 'primary' => 'Slate', 'success' => 'Verde', 'danger' => 'Vermelho', 'warning' => 'Amarelo', 'info' => 'Azul Claro', 'blue' => 'Azul', 'purple' => 'Roxo', 'pink' => 'Rosa', 'orange' => 'Laranja']),
                DatePicker::make('data_inicio')->label('Início')->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('data_previsao_fim')->label('Previsão de Fim')->native(false)->displayFormat('d/m/Y'),
                TextInput::make('valor_orcamento')->label('Orçamento Total da Obra')->numeric()->prefix('R$')
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                    ->stripCharacters('.')
                    ->extraInputAttributes(['type' => 'text'])
                    ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null)
                    ->helperText('Base para o % de avanço financeiro'),
                Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('status')->sortable()->label('Status')->badge()
                ->colors(['gray' => 'planejamento', 'info' => 'em_andamento', 'success' => 'concluido', 'danger' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado', default => $state }),
            TextColumn::make('data_inicio')->sortable()->label('Início')->date('d/m/Y')->placeholder('—'),
            TextColumn::make('data_previsao_fim')->sortable()->label('Previsão Fim')->date('d/m/Y')->placeholder('—'),
            TextColumn::make('avanco_fisico')->label('Avanço Físico')->state(fn ($record) => $record->avancoFisico().'%')->badge()
                ->color(fn ($record) => match (true) { $record->avancoFisico() >= 100 => 'success', $record->avancoFisico() >= 50 => 'info', $record->avancoFisico() > 0 => 'warning', default => 'gray' }),
        ])
        ->headerActions([ImportAction::make()->importer(ProjetoImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->dragReorderableColumns()->stickableColumns();
    }

    public static function getRelations(): array
    {
        return [UnidadesRelationManager::class, FasesObraRelationManager::class, OrcamentoItensRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjetos::route('/'),
            'create' => CreateProjeto::route('/create'),
            'edit' => EditProjeto::route('/{record}/edit'),
        ];
    }
}
