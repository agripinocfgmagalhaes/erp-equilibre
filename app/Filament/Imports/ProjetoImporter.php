<?php
namespace App\Filament\Imports;
use App\Models\Projeto;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

class ProjetoImporter extends Importer
{
    protected static ?string $model = Projeto::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nome')->requiredMapping()->rules(['required', 'max:150']),
            ImportColumn::make('descricao')->rules(['nullable']),
            ImportColumn::make('status')->rules(['nullable', 'in:planejamento,em_andamento,concluido,cancelado']),
            ImportColumn::make('data_inicio')->castStateUsing(fn ($state) => self::parseData($state))->rules(['nullable', 'date']),
            ImportColumn::make('data_previsao_fim')->castStateUsing(fn ($state) => self::parseData($state))->rules(['nullable', 'date']),
        ];
    }

    protected static function parseData(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
        } catch (\Throwable) {
            return $state;
        }
    }

    public function resolveRecord(): Projeto
    {
        return Projeto::firstOrNew(['nome' => $this->data['nome']]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de empreendimentos concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }
}
