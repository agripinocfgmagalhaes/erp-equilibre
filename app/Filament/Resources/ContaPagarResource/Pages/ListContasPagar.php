<?php
namespace App\Filament\Resources\ContaPagarResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\ContaPagarResource;
use App\Models\ContaPagar;
use App\Services\InterPixPagamentoService;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
class ListContasPagar extends ListRecords
{
    use HasToggleableTable, HasResizableColumn;
    protected static string $resource = ContaPagarResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título'),
            Action::make('atualizarPix')->label('Atualizar Pix')->icon('heroicon-o-arrow-path')->color('gray')
                ->action(function () {
                    $service = app(InterPixPagamentoService::class);
                    $pendentes = ContaPagar::whereNotNull('inter_pix_e2e_id')
                        ->where('status', '!=', 'pago')
                        ->where('status', '!=', 'cancelado')
                        ->get();
                    foreach ($pendentes as $conta) {
                        try {
                            $service->consultar($conta);
                        } catch (\Throwable $e) {
                            // ignora falha individual e segue os demais
                        }
                    }
                    Notification::make()->title($pendentes->count() . ' Pix verificado(s)')->success()->send();
                }),
        ];
    }
}
