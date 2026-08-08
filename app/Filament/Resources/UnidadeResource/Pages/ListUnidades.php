<?php
namespace App\Filament\Resources\UnidadeResource\Pages;

use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use App\Filament\Resources\UnidadeResource;
use App\Models\Unidade;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUnidades extends ListRecords
{
    use HasResizableColumn;

    protected static string $resource = UnidadeResource::class;
    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->slideOver()->label('+ Nova Unidade')];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')->badge(Unidade::count()),
            'disponivel' => Tab::make('Disponível')
                ->badge(Unidade::where('status', 'disponivel')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'disponivel')),
            'reservado' => Tab::make('Reservado')
                ->badge(Unidade::where('status', 'reservado')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'reservado')),
            'vendido' => Tab::make('Vendido')
                ->badge(Unidade::where('status', 'vendido')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'vendido')),
            'indisponivel' => Tab::make('Indisponível')
                ->badge(Unidade::where('status', 'indisponivel')->count())
                ->badgeColor('secondary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'indisponivel')),
            'distratado' => Tab::make('Distratado')
                ->badge(Unidade::where('status', 'distratado')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'distratado')),
        ];
    }
}
