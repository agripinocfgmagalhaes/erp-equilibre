<?php
namespace App\Filament\Imports;
use App\Models\Prestador;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PrestadorImporter extends Importer
{
    protected static ?string $model = Prestador::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nome')->requiredMapping()->rules(['required', 'max:150']),
            ImportColumn::make('cpf_cnpj')->rules(['nullable', 'max:18']),
            ImportColumn::make('email')->rules(['nullable', 'email', 'max:100']),
            ImportColumn::make('telefone')->rules(['nullable', 'max:20']),
            ImportColumn::make('especialidade')->rules(['nullable', 'max:100']),
            ImportColumn::make('observacoes')->rules(['nullable']),
            ImportColumn::make('ativo')->boolean()->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Prestador
    {
        if (! empty($this->data['cpf_cnpj'])) {
            return Prestador::firstOrNew(['cpf_cnpj' => $this->data['cpf_cnpj']]);
        }
        return new Prestador();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de prestadores concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
