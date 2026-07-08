<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ProjetoResource\Pages;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Empreendimentos';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'projetos';
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Dados do Empreendimento')->schema([
                Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(150)->columnSpanFull(),
                Forms\Components\Select::make('status')->label('Status')->native(false)->default('planejamento')
                    ->options(['planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado']),
                Forms\Components\DatePicker::make('data_inicio')->label('Início')->native(false)->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('data_previsao_fim')->label('Previsão de Fim')->native(false)->displayFormat('d/m/Y'),
                Forms\Components\Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Fases da Obra')->schema([
                TableRepeater::make('fasesObra')->relationship()->label('')
                    ->headers([Header::make('Nome')->width('300px'), Header::make('Ordem')->width('80px'), Header::make('% Conclusão')->width('120px')])
                    ->schema([
                        Forms\Components\TextInput::make('nome')->label('Nome')->required()->maxLength(100),
                        Forms\Components\TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
                        Forms\Components\TextInput::make('percentual')->label('%')->numeric()->step(0.01)->default(0),
                    ])->addActionLabel('+ Fase')->columnSpanFull()->defaultItems(0),
            ]),
            Section::make('Unidades')->schema([
                TableRepeater::make('unidades')->relationship()->label('')
                    ->headers([Header::make('ID')->width('100px'), Header::make('Tipo')->width('120px'), Header::make('Área (m²)')->width('100px'), Header::make('Valor Tabela')->width('150px'), Header::make('Status')->width('120px')])
                    ->schema([
                        Forms\Components\TextInput::make('identificacao')->label('ID')->required()->maxLength(20),
                        Forms\Components\TextInput::make('tipo')->label('Tipo')->maxLength(50),
                        Forms\Components\TextInput::make('area')->label('Área')->numeric()->step(0.01),
                        Forms\Components\TextInput::make('valor_tabela')->label('Valor')->numeric()->prefix('R$')->step(0.01)->default(0),
                        Forms\Components\Select::make('status')->label('Status')->native(false)->default('disponivel')
                            ->options(['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido', 'distratado' => 'Distratado']),
                    ])->addActionLabel('+ Unidade')->columnSpanFull()->defaultItems(0),
            ]),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->weight('medium'),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                ->colors(['gray' => 'planejamento', 'info' => 'em_andamento', 'success' => 'concluido', 'danger' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'planejamento' => 'Planejamento', 'em_andamento' => 'Em Andamento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado', default => $state }),
            Tables\Columns\TextColumn::make('data_inicio')->label('Início')->date('d/m/Y')->placeholder('—'),
            Tables\Columns\TextColumn::make('data_previsao_fim')->label('Previsão Fim')->date('d/m/Y')->placeholder('—'),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
        ->defaultSort('nome')->striped();
    }
    public static function getPages(): array
    {
        return ['index' => Pages\ListProjetos::route('/'), 'create' => Pages\CreateProjeto::route('/create'), 'edit' => Pages\EditProjeto::route('/{record}/edit')];
    }
}
