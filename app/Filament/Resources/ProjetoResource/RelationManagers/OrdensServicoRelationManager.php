<?php
namespace App\Filament\Resources\ProjetoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\RawJs;
use App\Models\Prestador;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class OrdensServicoRelationManager extends RelationManager
{
    protected static string $relationship = 'ordensServico';
    protected static ?string $title = 'Ordens de Serviço';
    protected static ?string $modelLabel = 'OS';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('numero')->label('Número da OS')->required()->unique(ignoreRecord: true)->maxLength(20),
            DatePicker::make('data')->label('Data')->required()->native(false)->displayFormat('d/m/Y'),
            Select::make('prestador_id')->label('Prestador/Empreiteiro')->native(false)->searchable()
                ->options(Prestador::orderBy('nome')->pluck('nome', 'id')),
            Select::make('fase_obra_id')->label('Fase da Obra')->native(false)
                ->relationship('faseObra', 'nome', fn ($q) => $q->where('projeto_id', $this->getOwnerRecord()->id)),
            TextInput::make('valor_total')->label('Valor Total')->numeric()->prefix('R$')->step(0.01)->required()
                ->mask(RawJs::make('$money($input, \',\', \'.\')'))->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(',', '.', $state) : null)
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null),
            DatePicker::make('data_inicio')->label('Início Previsto')->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_previsao_fim')->label('Fim Previsto')->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_conclusao')->label('Data Conclusão')->native(false)->displayFormat('d/m/Y'),
            Select::make('status')->label('Status')->native(false)->default('planejado')->required()
                ->options(['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso']),
            Textarea::make('descricao')->label('Descrição')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')->label('OS')->searchable()->sortable()->weight('medium')->width('80px'),
                TextColumn::make('data')->label('Data')->date('d/m/Y')->sortable(),
                TextColumn::make('prestador.nome')->label('Prestador')->searchable()->sortable(),
                TextColumn::make('valor_total')->label('Contratado')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('valor_medido')->label('Medido')->money('BRL')->alignEnd()
                    ->state(fn ($record) => 'R$ '.number_format($record->valorMedido(), 2, ',', '.')),
                TextColumn::make('percentual')->label('%')->alignEnd()->state(fn ($record) => $record->percentualMedido().'%'),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['gray' => 'planejado', 'info' => 'em_execucao', 'success' => 'concluido', 'danger' => 'suspenso'])
                    ->formatStateUsing(fn ($s) => ['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso'][$s] ?? $s),
            ])
            ->filters([SelectFilter::make('status')->options(['planejado' => 'Planejado', 'em_execucao' => 'Em Execução', 'concluido' => 'Concluído', 'suspenso' => 'Suspenso'])])
            ->headerActions([CreateAction::make()->label('+ OS')->slideOver()])
            ->recordActions([EditAction::make()->slideOver()->iconButton(), DeleteAction::make()->iconButton()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('numero');
    }
}
