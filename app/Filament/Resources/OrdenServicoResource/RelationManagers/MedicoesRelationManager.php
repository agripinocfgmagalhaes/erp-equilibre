<?php
namespace App\Filament\Resources\OrdenServicoResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\RawJs;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MedicoesRelationManager extends RelationManager
{
    protected static string $relationship = 'medicoes';
    protected static ?string $title = 'Medições';
    protected static ?string $modelLabel = 'Medição';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('numero')->label('Nº Medição')->numeric()
                ->default(fn ($livewire) => ($livewire->getOwnerRecord()->medicoes()->max('numero') ?? 0) + 1)
                ->disabled()->dehydrated(),
            DatePicker::make('data_medicao')->label('Data da Medição')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_inicio_periodo')->label('Período Início')->required()->native(false)->displayFormat('d/m/Y'),
            DatePicker::make('data_fim_periodo')->label('Período Fim')->required()->native(false)->displayFormat('d/m/Y'),
            TextInput::make('valor_total')->label('Valor Medido')->prefix('R$')->required()
                ->mask(RawJs::make('$money($input, \',\', \'.\')'))->extraInputAttributes(['type' => 'text'])
                ->dehydrateStateUsing(fn ($state) => $state !== null ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, ',', '.') : null),
            Select::make('status')->label('Status')->native(false)->default('rascunho')->required()
                ->options(['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga']),
            DatePicker::make('data_aprovacao')->label('Data Aprovação')->native(false)->displayFormat('d/m/Y'),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')->label('Med.')->sortable()->width('70px'),
                TextColumn::make('data_medicao')->label('Data Medição')->date('d/m/Y')->sortable(),
                TextColumn::make('data_inicio_periodo')->label('Período')->date('d/m/Y')->formatStateUsing(fn ($record) => $record->data_inicio_periodo->format('d/m/Y').' a '.$record->data_fim_periodo->format('d/m/Y')),
                TextColumn::make('valor_total')->label('Valor')->money('BRL')->alignEnd()->sortable(),
                TextColumn::make('percentual_acumulado')->label('% Acum.')->alignEnd()->suffix('%'),
                TextColumn::make('status')->label('Status')->badge()->sortable()
                    ->colors(['gray' => 'rascunho', 'warning' => 'medida', 'success' => 'aprovada', 'info' => 'faturada', 'gray' => 'paga'])
                    ->formatStateUsing(fn ($s) => ['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'][$s] ?? $s),
            ])
            ->filters([SelectFilter::make('status')->options(['rascunho' => 'Rascunho', 'medida' => 'Medida', 'aprovada' => 'Aprovada', 'faturada' => 'Faturada', 'paga' => 'Paga'])])
            ->headerActions([CreateAction::make()->label('+ Medição')->slideOver()])
            ->recordActions([
                EditAction::make()->slideOver()->iconButton(),
                Action::make('aprovar')->label('Aprovar')->icon('heroicon-o-check')->color('success')
                    ->visible(fn ($record) => $record->status === 'medida' && !$record->conta_pagar_id)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->aprovarEGerarContaPagar())
                    ->successNotification(\Filament\Notifications\Notification::make()->success()->title('Medição aprovada')),
                DeleteAction::make()->iconButton()->visible(fn ($record) => $record->status === 'rascunho'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('numero');
    }
}
