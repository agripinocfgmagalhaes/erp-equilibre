<?php
namespace App\Filament\Resources\ContaReceberResource\Pages;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\ContaReceberResource;
use App\Models\ContaReceber;
use App\Services\InterBoletoService;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListContasReceber extends ListRecords
{
    use HasToggleableTable;
    protected static string $resource = ContaReceberResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título'),
            Action::make('atualizarBoletos')->label('Atualizar Boletos')->icon('heroicon-o-arrow-path')->color('gray')
                ->action(function () {
                    $service = app(InterBoletoService::class);
                    $pendentes = ContaReceber::whereNotNull('inter_codigo_solicitacao')
                        ->where('inter_situacao', '!=', 'RECEBIDO')
                        ->get();
                    foreach ($pendentes as $conta) {
                        try {
                            $service->consultar($conta);
                        } catch (\Throwable $e) {
                            // ignora falha individual e segue os demais
                        }
                    }
                    Notification::make()->title($pendentes->count() . ' boleto(s) verificado(s)')->success()->send();
                }),
        ];
    }
}
