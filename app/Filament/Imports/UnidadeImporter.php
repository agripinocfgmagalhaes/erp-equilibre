<?php

namespace App\Filament\Imports;

use App\Models\Unidade;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UnidadeImporter extends Importer
{
    protected static ?string $model = Unidade::class;

    private const ALIASES = [
        'identificacao'   => ['identificacao', 'Identificação', 'IDENTIFICACAO', 'codigo', 'Código', 'unidade'],
        'valor_avaliado'  => ['valor_avaliado', 'VALOR AVALIADO', 'Valor Avaliado', 'valor avaliacao'],
        'valor_tabela'    => ['valor_tabela', 'Valor de Tabela', 'valor tabela', 'preco venda', 'preço venda', 'Preço de Venda'],
        'andar'           => ['andar', 'ANDAR', 'Andar'],
        'tipologia'       => ['tipologia', 'TIPOLOGIA', 'Tipologia'],
        'area'            => ['area', 'ÁREA PRIVATIVA', 'AREA PRIVATIVA', 'Area Privativa', 'área privativa'],
        'tipo'            => ['tipo', 'TIPO', 'Tipo'],
        'vaga_garagem'    => ['vaga_garagem', 'VAGA DE GARAGEM', 'Vaga de Garagem', 'vaga'],
        'status'          => ['status', 'Status', 'STATUS'],
    ];

    public static function getColumns(): array
    {
        // fillRecordUsing neutro: o preenchimento real (com parsing) é feito no resolveRecord.
        // Isso impede o Filament de gravar o valor cru ("R$ 220.500,00") na coluna decimal.
        $neutro = fn (Unidade $record, ?string $state) => null;

        return [
            ImportColumn::make('identificacao')
                ->requiredMapping()
                ->rules(['required', 'max:20'])
                ->fillRecordUsing($neutro),
            ImportColumn::make('valor_avaliado')->label('VALOR AVALIADO')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('valor_tabela')->label('Valor de Tabela')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('andar')->label('ANDAR')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('tipologia')->label('TIPOLOGIA')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('area')->label('ÁREA PRIVATIVA')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('tipo')->label('TIPO')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('vaga_garagem')->label('VAGA DE GARAGEM')->rules(['nullable'])->fillRecordUsing($neutro),
            ImportColumn::make('status')->label('Status')->rules(['nullable'])->fillRecordUsing($neutro),
        ];
    }

    public function resolveRecord(): Unidade
    {
        if (empty($this->options['projeto_id'])) {
            throw new RowImportFailedException('Empreendimento não selecionado.');
        }

        $get = function (string $campo): ?string {
            if (! empty($this->data[$campo])) return $this->data[$campo];
            foreach (self::ALIASES[$campo] ?? [] as $alias) {
                if (! empty($this->data[$alias])) return $this->data[$alias];
            }
            foreach ($this->data as $k => $v) {
                foreach (self::ALIASES[$campo] ?? [] as $alias) {
                    if (strcasecmp($k, $alias) === 0 && $v !== null && $v !== '') return $v;
                }
            }
            return null;
        };

        $ident = trim($get('identificacao') ?? '');
        if ($ident === '') {
            throw new RowImportFailedException('Linha sem identificação (código ou TIPO).');
        }

        try {
            $record = Unidade::where('projeto_id', $this->options['projeto_id'])
                ->where('identificacao', $ident)
                ->first() ?: new Unidade();

            $record->projeto_id = $this->options['projeto_id'];
            $record->identificacao = $ident;
            $record->valor_avaliado = self::parseDecimal($get('valor_avaliado'), true);
            $record->valor_tabela   = self::parseDecimal($get('valor_tabela'), true);
            $record->area           = self::parseDecimal($get('area'));
            $record->andar          = $get('andar') ? trim($get('andar')) : null;
            $record->tipologia      = $get('tipologia') ? trim($get('tipologia')) : null;
            $record->tipo           = $get('tipo') ? trim($get('tipo')) : null;
            $record->vaga_garagem   = $get('vaga_garagem') ? trim($get('vaga_garagem')) : null;

            $st = $get('status');
            if ($st) {
                $s = self::normalizeStatus($st);
                if (! in_array($s, ['disponivel', 'reservado', 'vendido', 'indisponivel'], true)) {
                    throw new \InvalidArgumentException("Status inválido: {$st}");
                }
                $record->status = $s;
            }

            return $record;
        } catch (\Throwable $e) {
            throw new RowImportFailedException($e->getMessage() ?: get_class($e));
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de unidades: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s).';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam — veja o relatório de erros.';
        }
        return $body;
    }

    private static function parseDecimal(?string $state, bool $moeda = false): ?float
    {
        if ($state === null || trim($state) === '') return null;
        $limpo = preg_replace('/[^0-9.,-]/', '', str_replace(['R$', ' '], '', $state));
        if ($limpo === '') return null;

        if (str_contains($limpo, ',')) {
            $limpo = str_replace('.', '', $limpo);
            $limpo = str_replace(',', '.', $limpo);
        } elseif ($moeda && preg_match('/^\d{1,3}(\.\d{3})+$/', $limpo)) {
            $limpo = str_replace('.', '', $limpo);
        }
        if (! is_numeric($limpo)) {
            throw new \InvalidArgumentException("Valor numérico inválido: {$state}");
        }
        return (float) $limpo;
    }

    private static function normalizeStatus(string $state): string
    {
        $s = mb_strtolower(trim($state));
        $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return str_replace([' ', '-', '_'], '', $s);
    }
}
