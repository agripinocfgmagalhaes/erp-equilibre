<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AdmissaoLinkResource\Pages\ListAdmissaoLinks;
use App\Filament\Resources\AdmissaoLinkResource\Pages\CreateAdmissaoLink;
use App\Models\AdmissaoLink;
use Filament\Resources\Resource;
use Filament\Tables\Table;
class AdmissaoLinkResource extends Resource
{
    protected static ?string $model = AdmissaoLink::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Links de Admissão';
    protected static string | \UnitEnum | null $navigationGroup = 'Pessoal';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'admissao-links';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('projeto_id')->label('Obra')->relationship('projeto', 'nome')->searchable()->preload()->required(),
            Toggle::make('ativo')->label('Ativo')->default(true),
            DatePicker::make('expira_em')->label('Expira em')->native(false)->displayFormat('d/m/Y'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('projeto.nome')->label('Obra')->placeholder('Todas'),
            TextColumn::make('token')->label('Link')->formatStateUsing(fn ($record) => $record->url())
                ->copyable()->copyMessage('Link copiado!')->limit(50),
            IconColumn::make('ativo')->label('Ativo')->boolean(),
            TextColumn::make('expira_em')->label('Expira em')->date('d/m/Y')->placeholder('Sem prazo'),
            TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->sortable(),
        ])
        ->recordActions([DeleteAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmissaoLinks::route('/'),
            'create' => CreateAdmissaoLink::route('/create'),
        ];
    }
}
