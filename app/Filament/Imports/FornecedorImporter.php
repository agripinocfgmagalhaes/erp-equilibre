<?php
namespace App\Filament\Imports;
use App\Models\Fornecedor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class FornecedorImporter extends Importer
{
    protected static ?string $model = Fornecedor::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nome')->requiredMapping()->rules(['required', 'max:150']),
            ImportColumn::make('cnpj')->rules(['nullable', 'max:20']),
            ImportColumn::make('email')->rules(['nullable', 'email', 'max:100']),
            ImportColumn::make('telefone')->rules(['nullable', 'max:20']),
            ImportColumn::make('contato')->rules(['nullable', 'max:100']),
            ImportColumn::make('observacoes')->rules(['nullable']),
            ImportColumn::make('ativo')->boolean()->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Fornecedor
    {
        if (! empty($this->data['cnpj'])) {
            return Fornecedor::firstOrNew(['cnpj' => $this->data['cnpj']]);
        }
        return new Fornecedor();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de fornecedores concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
