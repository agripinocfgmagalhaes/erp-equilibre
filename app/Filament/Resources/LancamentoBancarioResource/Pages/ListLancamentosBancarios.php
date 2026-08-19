<?php
namespace App\Filament\Resources\LancamentoBancarioResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\LancamentoBancarioResource;
use App\Models\LancamentoBancario;
use App\Models\ContaBancaria;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
class ListLancamentosBancarios extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = LancamentoBancarioResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    public function getTabs(): array
    {
        $tabs = [
            'todas' => Tab::make('Todas'),
        ];
        foreach (ContaBancaria::where('ativo', true)->orderBy('nome')->get() as $conta) {
            $tabs[(string) $conta->id] = Tab::make($conta->nome)
                ->modifyQueryUsing(fn ($query) => $query->where('conta_bancaria_id', $conta->id));
        }
        return $tabs;
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->label('+ Novo Lançamento'),
            Action::make('atualizarExtrato')->label('Atualizar Extrato')->icon('heroicon-o-arrow-path')->color('gray')
                ->action(function () {
                    try {
                        \Illuminate\Support\Facades\Artisan::call('financeiro:importar-extrato', ['--dias' => 7]);
                        Notification::make()->title('Extrato atualizado')->body(\Illuminate\Support\Facades\Artisan::output())->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Falha ao atualizar extrato')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('novaTransferencia')->label('Nova Transferência')->icon('heroicon-o-arrows-right-left')->color('gray')->slideOver()
                ->schema([
                    Select::make('conta_origem_id')->label('Conta de Origem')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                    Select::make('conta_destino_id')->label('Conta de Destino')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required()->different('conta_origem_id'),
                    TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                    DatePicker::make('data')->label('Data')->displayFormat('d/m/Y')->default(now())->required(),
                    TextInput::make('descricao')->label('Descrição')->maxLength(200)->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    LancamentoBancario::registrarTransferencia(
                        (int) $data['conta_origem_id'],
                        (int) $data['conta_destino_id'],
                        (float) $data['valor'],
                        $data['data'],
                        $data['descricao'] ?? ''
                    );
                    Notification::make()->title('Transferência registrada')->success()->send();
                }),
        ];
    }
}
