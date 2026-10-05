<?php
namespace App\Filament\Resources\ContaReceberResource\Pages;
use Asmit\ResizedColumn\HasResizableColumn;
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
use Filament\Actions\ExportAction;
use App\Filament\Exports\ContaReceberExporter;
class ListContasReceber extends ListRecords
{
    use HasToggleableTable, HasResizableColumn;
    protected static string $resource = ContaReceberResource::class;
    protected Width|string|null $maxContentWidth = 'full';
    public function getTabs(): array
    {
        $pendentes = ['aberto', 'vencido', 'parcial'];
        return [
            'todos' => \Filament\Schemas\Components\Tabs\Tab::make('Todos'),
            'em_aberto' => \Filament\Schemas\Components\Tabs\Tab::make('Em aberto')->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', $pendentes))
                ->badge(fn () => ContaReceber::whereIn('status', $pendentes)->count()),
            'vencidos' => \Filament\Schemas\Components\Tabs\Tab::make('Vencidos')->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', $pendentes)->whereDate('data_vencimento', '<', today()))
                ->badge(fn () => ContaReceber::whereIn('status', $pendentes)->whereDate('data_vencimento', '<', today())->count())
                ->badgeColor('danger'),
            'recebidos' => \Filament\Schemas\Components\Tabs\Tab::make('Recebidos')->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'recebido')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver()->modalWidth('4xl')->label('+ Novo Título'),
            Action::make('lancarParcelado')->label('Lançar Parcelado')->icon('heroicon-o-rectangle-stack')->color('success')
                ->modalWidth('3xl')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)->schema([
                        \Filament\Forms\Components\TextInput::make('descricao')->label('Descrição')->required()->maxLength(180)->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('numero_documento')->label('Nº Documento')->maxLength(40),
                        \Filament\Forms\Components\Select::make('cliente_id')->label('Cliente')->options(\App\Models\Cliente::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false),
                        \Filament\Forms\Components\Select::make('plano_conta_id')->label('Plano de Conta')->options(\App\Models\PlanoConta::where('tipo', 'receita')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false),
                        \Filament\Forms\Components\Select::make('conta_bancaria_id')->label('Conta Bancária')->options(\App\Models\ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false),
                        \Filament\Forms\Components\Select::make('projeto_id')->label('Empreendimento')->options(\App\Models\Projeto::pluck('nome', 'id'))->searchable()->native(false),
                        \Filament\Forms\Components\TextInput::make('valor')->label('Valor da parcela')->numeric()->prefix('R$')->step(0.01)->minValue(0.01)->required(),
                        \Filament\Forms\Components\TextInput::make('parcelas')->label('Qtd. de parcelas')->numeric()->minValue(1)->maxValue(360)->default(12)->required(),
                        \Filament\Forms\Components\DatePicker::make('primeiro_vencimento')->label('1º vencimento')->displayFormat('d/m/Y')->required(),
                    ]),
                ])
                ->action(function (array $d) {
                    $n = (int) $d['parcelas'];
                    $base = \Carbon\Carbon::parse($d['primeiro_vencimento']);
                    \Illuminate\Support\Facades\DB::transaction(function () use ($d, $n, $base) {
                        for ($i = 0; $i < $n; $i++) {
                            $seq = ($i + 1) . '/' . $n;
                            ContaReceber::create([
                                'descricao' => $d['descricao'] . ' (' . $seq . ')',
                                'numero_documento' => ! empty($d['numero_documento']) ? $d['numero_documento'] . '-' . $seq : null,
                                'cliente_id' => $d['cliente_id'] ?? null,
                                'plano_conta_id' => $d['plano_conta_id'] ?? null,
                                'conta_bancaria_id' => $d['conta_bancaria_id'] ?? null,
                                'projeto_id' => $d['projeto_id'] ?? null,
                                'valor' => $d['valor'],
                                'valor_recebido' => 0,
                                'data_vencimento' => $base->copy()->addMonthsNoOverflow($i)->toDateString(),
                                'status' => 'aberto',
                            ]);
                        }
                    });
                    Notification::make()->title($n . ' parcela(s) lançada(s)')->success()->send();
                }),
            ExportAction::make()->label('Exportar Relatório')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->exporter(ContaReceberExporter::class),
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
