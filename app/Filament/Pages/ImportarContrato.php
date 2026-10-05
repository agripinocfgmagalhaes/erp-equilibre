<?php
namespace App\Filament\Pages;

use App\Services\ImportarContratoPlanilhaService;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class ImportarContrato extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Importar Contrato';
    protected static ?string $title = 'Importar Contrato (colar linha da planilha)';
    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = 'importar-contrato';

    protected string $view = 'filament.pages.importar-contrato';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('linha')
                ->label('Cole aqui a linha inteira da planilha (com os TABs, direto da célula do Google Sheets)')
                ->rows(6)
                ->required()
                ->helperText('Selecione a linha inteira na planilha (Ctrl+C) e cole aqui — não precisa formatar nada.'),
        ])->statePath('data')->columns(1);
    }

    public function importar(): void
    {
        $data = $this->form->getState();

        try {
            $contrato = app(ImportarContratoPlanilhaService::class)->importar($data['linha']);

            Notification::make()
                ->title('Contrato importado: ' . $contrato->numero)
                ->body('Cliente: ' . $contrato->cliente->nome . ' — Unidade: ' . $contrato->unidade->identificacao . '. Revise os valores antes de gerar as parcelas (botão "Gerar CR").')
                ->success()
                ->send();

            $this->redirect(\App\Filament\Resources\ContratoVendaResource::getUrl('index', ['tableSearch' => $contrato->numero]));
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Falha ao importar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
