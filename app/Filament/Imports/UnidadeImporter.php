<?php
namespace App\Filament\Imports;
use App\Models\Unidade;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UnidadeImporter extends Importer
{
    protected static ?string $model = Unidade::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('identificacao')->requiredMapping()->rules(['required', 'max:20']),
            ImportColumn::make('tipo')->rules(['nullable', 'max:50']),
            ImportColumn::make('area')->numeric()->rules(['nullable', 'numeric']),
            ImportColumn::make('valor_tabela')->numeric()->rules(['nullable', 'numeric']),
            ImportColumn::make('status')->rules(['nullable', 'in:disponivel,reservado,vendido,distratado']),
        ];
    }

    public function resolveRecord(): Unidade
    {
        return Unidade::firstOrNew([
            'projeto_id' => $this->options['projeto_id'],
            'identificacao' => $this->data['identificacao'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de unidades concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
