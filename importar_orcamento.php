<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\Projeto;
use Illuminate\Support\Facades\DB;

$csv = $argv[1] ?? null;
$projetoNome = $argv[2] ?? '';
if (! $csv || ! file_exists($csv)) { echo "Uso: php importar_orcamento.php arquivo.csv \"Costa Azul\"\n"; exit(1); }
$projeto = Projeto::where('nome', 'like', '%'.$projetoNome.'%')->first();
if (! $projeto) { echo "Projeto não encontrado\n"; exit(1); }

function num(?string $v): ?float {
    if ($v === null) return null;
    $v = trim(str_replace(['R$', ' ', '%'], '', $v));
    if ($v === '' || $v === '-' || $v === '—') return null;
    if (str_contains($v, ',')) { $v = str_replace('.', '', $v); $v = str_replace(',', '.', $v); }
    return is_numeric($v) ? (float) $v : null;
}

$mesesMap = ['jan'=>1,'fev'=>2,'mar'=>3,'abr'=>4,'mai'=>5,'jun'=>6,'jul'=>7,'ago'=>8,'set'=>9,'out'=>10,'nov'=>11,'dez'=>12];

$h = fopen($csv, 'r');
$rows = [];
while (($r = fgetcsv($h, 0, ',')) !== false) $rows[] = $r;
fclose($h);

// Detecta linha dos meses (mar.-26 etc.)
$mesCols = [];
foreach ($rows as $r) {
    $achados = 0;
    foreach ($r as $j => $c) if (preg_match('/^([a-z]{3})\.?-(\d{2})$/i', trim($c ?? ''))) $achados++;
    if ($achados >= 6) {
        foreach ($r as $j => $c) {
            if (preg_match('/^([a-z]{3})\.?-(\d{2})$/i', trim($c ?? ''), $m)) {
                $mes = $mesesMap[strtolower(substr($m[1], 0, 3))] ?? null;
                if ($mes) $mesCols[$j] = sprintf('%04d-%02d-01', 2000 + (int) $m[2], $mes);
            }
        }
        break;
    }
}
echo 'Meses do cronograma detectados: '.count($mesCols)."\n";

// Início dos dados: linha após o cabeçalho #/Descricao
$start = null;
foreach ($rows as $i => $r) {
    if (trim($r[0] ?? '') === '#' || strtolower(trim($r[1] ?? '')) === 'descricao') { $start = $i + 1; break; }
}
if ($start === null) { echo "Cabeçalho não encontrado\n"; exit(1); }

// data_base + área + unidades (varre tudo)
$dataBase = null; $area = null; $unidades = null;
foreach ($rows as $r) {
    foreach ($r as $j => $c) {
        $t = trim($c ?? '');
        if ($t === 'DATA') $dataBase = \Carbon\Carbon::createFromFormat('n/j/Y', trim($r[$j+1] ?? ''))->toDateString();
        if (mb_strtoupper($t) === 'ÁREA CONSTRUÍDA') $area = num($r[$j+1] ?? null);
        if (mb_strtoupper($t) === 'Nº DE UNIDADES') $unidades = (int) num($r[$j+1] ?? null);
    }
}

$orc = Orcamento::updateOrCreate(
    ['projeto_id' => $projeto->id, 'nome' => 'Orçamento Padrão'],
    ['data_base' => $dataBase, 'area_construida' => $area, 'numero_unidades' => $unidades, 'status' => 'rascunho']
);
$orc->itens()->delete();
$orc->cronograma()->delete();

$mapIds = [];      // codigo => id
$secaoAtual = null;
$seqExtra = [];    // secao => contador de itens sem código
$itens = 0; $crono = 0;

for ($i = $start; $i < count($rows); $i++) {
    $r = $rows[$i];
    $cod = trim($r[0] ?? '');
    $desc = trim($r[1] ?? '');
    if ($cod === '' && $desc === '') continue;
    if (mb_strtoupper($desc) === 'TOTAL' || mb_strtoupper($cod) === 'TOTAL') break;

    $unid = trim($r[2] ?? '');
    $qtd = num($r[3] ?? null);
    $matU = num($r[4] ?? null);
    $mdoU = num($r[5] ?? null);
    $outU = num($r[6] ?? null);

    $ehSecao = ($unid === '' && $qtd === null && $matU === null && $mdoU === null && $outU === null);

    if ($ehSecao) {
        $item = OrcamentoItem::create([
            'orcamento_id' => $orc->id, 'parent_id' => null, 'codigo' => $cod,
            'descricao' => $desc, 'tipo' => 'secao',
        ]);
        if ($cod !== '') { $mapIds[$cod] = $item->id; $secaoAtual = $item->id; }
        continue;
    }

    // Item (ou subitem)
    if ($cod === '') {
        $seqExtra[$secaoAtual] = ($seqExtra[$secaoAtual] ?? 50) + 1;
        $cod = 'x'.$seqExtra[$secaoAtual];
    }
    $parentId = $secaoAtual;
    if (str_contains($cod, '.')) {
        $prefixo = substr($cod, 0, strrpos($cod, '.'));
        if (isset($mapIds[$prefixo])) $parentId = $mapIds[$prefixo];
    }

    $defs = [];
    if ($matU) $defs[] = ['material', $matU];
    if ($mdoU) $defs[] = ['mdo', $mdoU];
    if ($outU) $defs[] = ['outros', $outU];
    if (! $defs) $defs[] = [null, 0];

    foreach ($defs as $d) {
        $item = OrcamentoItem::create([
            'orcamento_id' => $orc->id, 'parent_id' => $parentId, 'codigo' => $cod,
            'descricao' => $desc, 'tipo' => 'item', 'unidade' => $unid ?: null,
            'quantidade' => $qtd, 'classificacao' => $d[0], 'custo_unitario' => $d[1],
        ]);
        $itens++;
    }
    $mapIds[$cod] = $item->id;

    // Cronograma físico (% mensais) — uma vez por código
    foreach ($mesCols as $j => $comp) {
        $p = num($r[$j] ?? null);
        if ($p !== null && $p > 0) {
            DB::table('orcamento_cronograma')->insert([
                'orcamento_id' => $orc->id, 'codigo_item' => $cod,
                'competencia' => $comp, 'percentual' => $p,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $crono++;
        }
    }
}

echo "\nImportado: {$itens} linhas de item | {$crono} apontamentos de cronograma\n";
echo 'Total Material: R$ '.number_format($orc->totalPorClassificacao('material'), 2, ',', '.')."\n";
echo 'Total MDO:      R$ '.number_format($orc->totalPorClassificacao('mdo'), 2, ',', '.')."\n";
echo 'Total Outros:   R$ '.number_format($orc->totalPorClassificacao('outros'), 2, ',', '.')."\n";
echo 'TOTAL GERAL:    R$ '.number_format($orc->totalGeral(), 2, ',', '.')."\n";
if ($area) echo 'Custo/m²:       R$ '.number_format($orc->totalGeral() / $area, 2, ',', '.')."\n";
if ($unidades) echo 'Custo/unidade:  R$ '.number_format($orc->totalGeral() / $unidades, 2, ',', '.')."\n";
