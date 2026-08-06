<?php
namespace App\Filament\Resources\ProjetoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use App\Models\FasePadrao;

class FasesObraRelationManager extends RelationManager
{
    protected static string $relationship = 'fasesObra';
    protected static ?string $title = 'Fases da Obra';
    protected static ?string $modelLabel = 'Fase';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('fase_padrao_id')->label('Fase (catálogo)')->native(false)->searchable()
                ->options(fn () => FasePadrao::orderBy('ordem')->pluck('nome', 'id'))
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) return;
                    $fp = FasePadrao::find($state);
                    if ($fp) { $set('nome', $fp->nome); $set('ordem', $fp->ordem); }
                })->live(),
            TextInput::make('nome')->label('Nome')->required()->maxLength(100),
            TextInput::make('ordem')->label('Ordem')->numeric()->default(0),
            TextInput::make('peso')->label('Peso')->numeric()->step(0.01)->default(0)->suffix('%')
                ->helperText('Peso da fase no avanço físico total. Some 100% entre as fases.'),
            TextInput::make('percentual')->label('% Conclusão')->numeric()->step(0.01)->default(0)->minValue(0)->maxValue(100)->suffix('%'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                TextColumn::make('ordem')->label('Ordem')->sortable()->width('80px'),
                TextColumn::make('nome')->label('Fase')->searchable()->weight('medium'),
                TextColumn::make('peso')->label('Peso')->suffix('%')->alignEnd()->sortable(),
                TextColumn::make('percentual')->label('% Conclusão')->suffix('%')->alignEnd()->sortable()
                    ->badge()->color(fn ($state) => match (true) { $state >= 100 => 'success', $state >= 50 => 'info', $state > 0 => 'warning', default => 'gray' }),
            ])
            ->headerActions([
                Action::make('importarCatalogo')->label('Importar do Catálogo')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->action(function () {
                        $projeto = $this->getOwnerRecord();
                        $existentes = $projeto->fasesObra()->pluck('fase_padrao_id')->filter()->toArray();
                        $paraImportar = FasePadrao::whereNotIn('id', $existentes)->orderBy('ordem')->get();
                        foreach ($paraImportar as $fp) {
                            $projeto->fasesObra()->create([
                                'fase_padrao_id' => $fp->id,
                                'nome' => $fp->nome,
                                'ordem' => $fp->ordem,
                                'peso' => 0,
                                'percentual' => 0,
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->modalDescription('Importa todas as fases do catálogo que ainda não estão neste projeto, com peso 0% (ajuste depois).'),
                CreateAction::make()->label('+ Fase')->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->reorderable('ordem')
            ->defaultSort('ordem');
    }
}
