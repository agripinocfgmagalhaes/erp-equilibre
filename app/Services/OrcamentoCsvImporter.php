<?php
namespace App\Services;

use App\Models\FasePadrao;
use App\Models\OrcamentoItem;
use App\Models\OrcamentoItemCronograma;
use Illuminate\Support\Facades\DB;

class OrcamentoCsvImporter
{
    private const MESES = [
        'jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4, 'mai' => 5, 'jun' => 6,
        'jul' => 7, 'ago' => 8, 'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12,
    ];

    /**
     * @return array{itens: int, cronograma: int}
     */
    public function importar(int $projetoId, string $caminho): array
    {
        if (! is_file($caminho)) {
            throw new \RuntimeException("Arquivo nao encontrado: {$caminho}");
        }

        $handle = fopen($caminho, 'r');
        $rows = [];
        while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (count($rows) < 5) {
            throw new \RuntimeException('CSV sem linhas suficientes.');
        }

        $meses = [];
        foreach ($rows[2] as $i => $v) {
            $v = trim((string) $v);
            if (preg_match('/^([a-z]{3})\.-(\d{2})$/i', $v, $m)) {
                $meses[$i] = strtolower($m[1]) . '-' . $m[2];
            }
        }

        if (empty($meses)) {
            throw new \RuntimeException('Nao foi possivel detectar as colunas de meses no cabecalho.');
        }

        $totalItens = 0;
        $totalCronograma = 0;
        $faseAtual = null;
        $ordemFase = 0;
        $ordemItem = 0;

        DB::transaction(function () use (
            $rows, $meses, $projetoId,
            &$faseAtual, &$ordemFase, &$ordemItem, &$totalItens, &$totalCronograma
        ) {
            foreach (array_slice($rows, 4) as $row) {
                $col0 = trim((string) ($row[0] ?? ''));
                $col1 = trim((string) ($row[1] ?? ''));

                if ($col0 === '') {
                    continue;
                }

                if (ctype_digit($col0)) {
                    $ordemFase++;
                    $faseAtual = FasePadrao::firstOrCreate(
                        ['nome' => mb_strtoupper($col1)],
                        ['ordem' => $ordemFase]
                    );
                    continue;
                }

                if (! $faseAtual) {
                    continue;
                }

                $ordemItem++;

                $item = OrcamentoItem::updateOrCreate(
                    ['projeto_id' => $projetoId, 'numero_item' => $col0],
                    [
                        'fase_padrao_id' => $faseAtual->id,
                        'ordem' => $ordemItem,
                        'descricao' => $col1,
                        'unidade' => trim((string) ($row[2] ?? '')) ?: null,
                        'quantidade' => self::num($row[3] ?? null) ?? 0,
                        'material_unitario' => self::num($row[4] ?? null) ?? 0,
                        'mdo_unitario' => self::num($row[5] ?? null) ?? 0,
                        'outros_unitario' => self::num($row[6] ?? null) ?? 0,
                    ]
                );
                $totalItens++;

                foreach ($meses as $idx => $mesLabel) {
                    $pct = self::pct($row[$idx] ?? null);
                    if ($pct === null || $pct <= 0) {
                        continue;
                    }

                    OrcamentoItemCronograma::updateOrCreate(
                        ['orcamento_item_id' => $item->id, 'mes' => self::mesParaData($mesLabel)],
                        ['percentual' => $pct]
                    );
                    $totalCronograma++;
                }
            }
        });

        return ['itens' => $totalItens, 'cronograma' => $totalCronograma];
    }

    private static function num($v): ?float
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        $v = str_replace(["\xC2\xA0", ' '], '', $v);
        if (preg_match('/^-+$/', $v)) {
            return 0.0;
        }
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float) $v : null;
    }

    private static function pct($v): ?float
    {
        $v = trim((string) $v);
        if ($v === '' || $v === '-') {
            return null;
        }
        $v = str_replace('%', '', $v);
        $v = str_replace(["\xC2\xA0", ' '], '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float) $v : null;
    }

    private static function mesParaData(string $label): string
    {
        [$mes, $ano] = explode('-', $label);
        $anoCompleto = 2000 + (int) $ano;
        $mesNum = self::MESES[$mes] ?? 1;
        return sprintf('%04d-%02d-01', $anoCompleto, $mesNum);
    }
}
