<?php
namespace App\Filament\Imports;
use App\Models\Produto;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProdutoImporter extends Importer
{
    protected static ?string $model = Produto::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('codigo')->rules(['nullable', 'max:30']),
            ImportColumn::make('nome')->requiredMapping()->rules(['required', 'max:150']),
            ImportColumn::make('unidade')->rules(['nullable', 'max:10']),
            ImportColumn::make('categoria')->rules(['nullable', 'max:100']),
            ImportColumn::make('preco_referencia')->numeric()->rules(['nullable', 'numeric']),
            ImportColumn::make('ativo')->boolean()->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Produto
    {
        if (! empty($this->data['codigo'])) {
            return Produto::firstOrNew(['codigo' => $this->data['codigo']]);
        }
        return new Produto();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de produtos concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
