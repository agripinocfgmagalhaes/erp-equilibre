<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use App\Models\FaseObra;
use App\Models\FasePadrao;
use App\Models\Servico;
use App\Models\OrcamentoItem;
use App\Services\OrcamentoCsvImporter;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewProjeto extends ViewRecord
{
    protected static string $resource = ProjetoResource::class;

    protected string $view = 'filament.resources.projetos.view';

    public $faseItens = null;
    public $faseAvanco = null;
    public $percentualNovo = null;
    public $itemId = null;
    public $itemDescricao = '';
    public $itemUnidade = '';
    public $itemQuantidade = null;
    public $itemValor = null;
    public $itemTipo = 'material';
    public $itemServicoId = null;
    
    public $arquivoCsv = null;

    public function getTitle(): string
    {
        return $this->getRecord()->nome.' — Obra';
    }

    protected function getHeaderActions(): array
    {
        return [EditAction::make()->slideOver()->modalWidth('4xl')->label('Editar empreendimento')];
    }

    public function dados(): array
    {
        $r = $this->getRecord();
        $d = $r->painelCustoPorFase();
        $d['vgv'] = (float) $r->unidades()->sum('valor_tabela');
        $d['margem'] = $d['vgv'] - $d['total_orcado'];
        return $d;
    }

    public function nomeFaseItens(): string
    {
        return $this->faseItens ? (FasePadrao::find($this->faseItens)->nome ?? '') : '';
    }

    public function itensDaFase()
    {
        if (! $this->faseItens) return collect();
        return OrcamentoItem::where('projeto_id', $this->getRecord()->id)
            ->where('fase_padrao_id', $this->faseItens)->orderBy('id')->get();
    }

    public function abrirItens($fasePadraoId) { $this->faseItens = $fasePadraoId; $this->resetItemForm(); }
    public function fecharItens() { $this->faseItens = null; $this->resetItemForm(); }

    public function abrirAvanco($faseObraId)
    {
        $this->faseAvanco = $faseObraId;
        $this->percentualNovo = (string) (FaseObra::find($faseObraId)->percentual ?? 0);
    }
    public function fecharAvanco() { $this->faseAvanco = null; }

    public function salvarAvanco()
    {
        FaseObra::where('id', $this->faseAvanco)->update(['percentual' => (float) str_replace(',', '.', (string) $this->percentualNovo)]);
        $this->fecharAvanco();
        Notification::make()->title('Avanço atualizado')->success()->send();
    }

    public function editarItem($id)
    {
        $i = OrcamentoItem::find($id);
        $this->itemId = $i->id;
        $this->itemDescricao = $i->descricao;
        $this->itemUnidade = $i->unidade;
        $this->itemQuantidade = (string) $i->quantidade;
        $this->itemValor = (string) $i->valor_unitario;
        $this->itemServicoId = $i->servico_id;
        $this->itemTipo = $i->tipo ?? 'material';
    }

    public function salvarItem()
    {
        $q = (float) str_replace(',', '.', (string) $this->itemQuantidade);
        $v = (float) str_replace(',', '.', (string) $this->itemValor);
        $dados = ['servico_id' => $this->itemServicoId ?: null, 'tipo' => $this->itemTipo, 'descricao' => $this->itemDescricao, 'unidade' => $this->itemUnidade, 'quantidade' => $q, 'valor_unitario' => $v, 'valor_total' => $q * $v];
        if ($this->itemId) {
            OrcamentoItem::where('id', $this->itemId)->update($dados);
        } else {
            OrcamentoItem::create(array_merge($dados, ['projeto_id' => $this->getRecord()->id, 'fase_padrao_id' => $this->faseItens]));
        }
        $this->resetItemForm();
        Notification::make()->title('Item salvo')->success()->send();
    }

    public function excluirItem($id)
    {
        DB::table('orcamento_item_cronograma')->where('orcamento_item_id', $id)->delete();
        OrcamentoItem::where('id', $id)->delete();
        $this->resetItemForm();
        Notification::make()->title('Item excluído')->success()->send();
    }

    public function resetItemForm()
    {
        $this->itemId = null; $this->itemDescricao = ''; $this->itemUnidade = '';
        $this->itemQuantidade = null; $this->itemValor = null; $this->itemServicoId = null; $this->itemTipo = 'material';
    }

    public function servicos()
    {
        return Servico::where('ativo', true)->orderBy('nome')->get();
    }

    public function preencherServico()
    {
        if (! $this->itemServicoId) return;
        $s = Servico::find($this->itemServicoId);
        if ($s) {
            $this->itemDescricao = $s->nome;
            $this->itemUnidade = $s->unidade_padrao;
        }
    }

    public function importarCsv()
    {
        if (! $this->arquivoCsv) { Notification::make()->title('Escolha o arquivo CSV')->warning()->send(); return; }
        try {
            $r = $this->getRecord();
            DB::table('orcamento_item_cronograma')->whereIn('orcamento_item_id', fn ($q) => $q->select('id')->from('orcamento_itens')->where('projeto_id', $r->id))->delete();
            DB::table('orcamento_itens')->where('projeto_id', $r->id)->delete();
            $res = app(OrcamentoCsvImporter::class)->importar($r->id, $this->arquivoCsv->getRealPath());
            $this->arquivoCsv = null;
            Notification::make()->title('Orçamento importado')->body("{$res['itens']} itens, {$res['cronograma']} linhas de cronograma.")->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Erro ao importar')->body($e->getMessage())->danger()->send();
        }
    }
}
