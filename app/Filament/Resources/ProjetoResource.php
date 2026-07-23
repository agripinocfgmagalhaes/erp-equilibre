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
use App\Filament\Resources\ProjetoResource\Pages;
use App\Filament\Imports\ProjetoImporter;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Empreendimentos';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'projetos';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Empreendimento')->schema([
                TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
                Select::make('status')->label('Status')->native(false)->default('planejamento')
                    ->options(['planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado']),
                DatePicker::make('data_inicio')->label('Início')->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('data_previsao_fim')->label('Previsão de Fim')->native(false)->displayFormat('d/m/Y'),
                Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Fases da Obra')->schema([
                TableRepeater::make('fasesObra')->relationship()->label('')
                    ->headers([Header::make('Nome')->width('300px'), Header::make('Ordem')->width('80px'), Header::make('% Conclusão')->width('120px')])
                    ->schema([
                        TextInput::make('nome')->label('Nome')->required()->maxLength(100),
                        TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
                        TextInput::make('percentual')->label('%')->numeric()->step(0.01)->default(0),
                    ])->addActionLabel('+ Fase')->columnSpanFull()->defaultItems(0),
            ]),
            Section::make('Unidades')->schema([
                TableRepeater::make('unidades')->relationship()->label('')
                    ->headers([Header::make('ID')->width('100px'), Header::make('Tipo')->width('120px'), Header::make('Área (m²)')->width('100px'), Header::make('Valor Tabela')->width('150px'), Header::make('Status')->width('120px')])
                    ->schema([
                        TextInput::make('identificacao')->label('ID')->required()->maxLength(20),
                        TextInput::make('tipo')->label('Tipo')->maxLength(50),
                        TextInput::make('area')->label('Área')->numeric()->step(0.01),
                        TextInput::make('valor_tabela')->label('Valor')->numeric()->prefix('R$')->step(0.01)->default(0),
                        Select::make('status')->label('Status')->native(false)->default('disponivel')
                            ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado']),
                    ])->addActionLabel('+ Unidade')->columnSpanFull()->defaultItems(0),
            ]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'planejamento', 'info' => 'em_andamento', 'success' => 'concluido', 'danger' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado', default => $state }),
            TextColumn::make('data_inicio')->label('Início')->date('d/m/Y')->placeholder('—'),
            TextColumn::make('data_previsao_fim')->label('Previsão Fim')->date('d/m/Y')->placeholder('—'),
        ])
        ->headerActions([ImportAction::make()->importer(ProjetoImporter::class)->label('Importar Planilha')])
        ->recordActions([EditAction::make(), DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => ListProjetos::route('/'), 'create' => CreateProjeto::route('/create'), 'edit' => EditProjeto::route('/{record}/edit')];
    }
}
