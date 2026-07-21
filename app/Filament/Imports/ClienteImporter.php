<?php
namespace App\Filament\Imports;
use App\Models\Cliente;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ClienteImporter extends Importer
{
    protected static ?string $model = Cliente::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nome')->requiredMapping()->rules(['required', 'max:150']),
            ImportColumn::make('cpf')->rules(['nullable', 'max:14']),
            ImportColumn::make('email')->rules(['nullable', 'email', 'max:100']),
            ImportColumn::make('telefone')->rules(['nullable', 'max:20']),
            ImportColumn::make('celular')->rules(['nullable', 'max:20']),
            ImportColumn::make('endereco')->rules(['nullable', 'max:200']),
            ImportColumn::make('bairro')->rules(['nullable', 'max:100']),
            ImportColumn::make('cidade')->rules(['nullable', 'max:100']),
            ImportColumn::make('estado')->rules(['nullable', 'max:2']),
            ImportColumn::make('cep')->rules(['nullable', 'max:9']),
            ImportColumn::make('observacoes')->rules(['nullable']),
            ImportColumn::make('ativo')->boolean()->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Cliente
    {
        if (! empty($this->data['cpf'])) {
            return Cliente::firstOrNew(['cpf' => $this->data['cpf']]);
        }
        return new Cliente();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de clientes concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
