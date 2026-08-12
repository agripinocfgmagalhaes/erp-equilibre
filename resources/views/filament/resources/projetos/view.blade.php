<x-filament-panels::page>
@php
    $d = $this->dados();
    $fmt = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $corDesvio = fn ($v) => $v > 0 ? '#dc2626' : ($v < 0 ? '#16a34a' : '#6b7280');
    $corBarra = fn ($p) => $p >= 80 ? '#22c55e' : ($p >= 50 ? '#eab308' : ($p > 0 ? '#3b82f6' : '#d1d5db'));
    $card = 'border:1px solid #e5e7eb;border-radius:8px;background:#fff;padding:12px 16px;';
    $btn = 'border:1px solid #d1d5db;background:#fff;border-radius:6px;padding:3px 10px;font-size:12px;cursor:pointer;';
@endphp

{{-- Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px;">
    <div style="{{ $card }}border-left:4px solid #0ea5e9;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;">VGV (vendas)</div>
        <div style="font-size:17px;font-weight:700;">{{ $fmt($d['vgv']) }}</div>
    </div>
    <div style="{{ $card }}border-left:4px solid #6b7280;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;">Orçado</div>
        <div style="font-size:17px;font-weight:700;">{{ $fmt($d['total_orcado']) }}</div>
    </div>
    <div style="{{ $card }}border-left:4px solid #3b82f6;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;">Realizado</div>
        <div style="font-size:17px;font-weight:700;">{{ $fmt($d['total_realizado']) }}</div>
    </div>
    <div style="{{ $card }}border-left:4px solid {{ $d['margem'] >= 0 ? '#16a34a' : '#dc2626' }};">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;">Margem prevista</div>
        <div style="font-size:17px;font-weight:700;color:{{ $d['margem'] >= 0 ? '#16a34a' : '#dc2626' }};">{{ $fmt($d['margem']) }}</div>
    </div>
    <div style="{{ $card }}border-left:4px solid #8b5cf6;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;">Avanço físico</div>
        <div style="font-size:17px;font-weight:700;">{{ number_format($d['avanco_fisico'], 1, ',', '.') }}%</div>
        <div style="margin-top:6px;background:#e5e7eb;border-radius:4px;height:6px;">
            <div style="width:{{ min(100, $d['avanco_fisico']) }}%;background:{{ $corBarra($d['avanco_fisico']) }};height:6px;border-radius:4px;"></div>
        </div>
    </div>
</div>

{{-- Importação --}}
<div style="margin-bottom:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <input type="file" accept=".csv" wire:model="arquivoCsv" style="font-size:13px;">
    <button wire:click="importarCsv" style="{{ $btn }}background:#16a34a;color:#fff;border:none;padding:6px 14px;">Importar orçamento (CSV)</button>
    <span wire:loading wire:target="arquivoCsv" style="font-size:12px;color:#6b7280;">enviando arquivo…</span>
</div>

{{-- Tabela de fases --}}
<div style="border:1px solid #e5e7eb;border-radius:8px;background:#fff;overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:8px 12px;text-align:left;">Fase</th>
                <th style="padding:8px 12px;text-align:right;">Orçado</th>
                <th style="padding:8px 12px;text-align:right;">Realizado</th>
                <th style="padding:8px 12px;text-align:right;">Desvio</th>
                <th style="padding:8px 12px;text-align:center;">Peso</th>
                <th style="padding:8px 12px;text-align:left;min-width:170px;">Avanço (clique p/ editar)</th>
                <th style="padding:8px 12px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($d['linhas'] as $l)
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:7px 12px;"><span style="color:#9ca3af;">{{ $l['ordem'] }}.</span> {{ $l['nome'] }}</td>
                    <td style="padding:7px 12px;text-align:right;">{{ $fmt($l['orcado']) }}</td>
                    <td style="padding:7px 12px;text-align:right;">{{ $fmt($l['realizado']) }}</td>
                    <td style="padding:7px 12px;text-align:right;font-weight:600;color:{{ $corDesvio($l['desvio']) }};">{{ $fmt($l['desvio']) }}</td>
                    <td style="padding:7px 12px;text-align:center;font-size:12px;color:#6b7280;">{{ number_format($l['peso'], 1, ',', '.') }}%</td>
                    <td style="padding:7px 12px;">
                        <button wire:click="abrirAvanco({{ $l['fase_obra_id'] }})" style="display:flex;align-items:center;gap:8px;width:100%;background:none;border:none;cursor:pointer;padding:0;">
                            <span style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;display:block;">
                                <span style="width:{{ min(100, $l['perc']) }}%;background:{{ $corBarra($l['perc']) }};height:8px;border-radius:4px;display:block;"></span>
                            </span>
                            <span style="font-size:12px;font-weight:600;width:48px;text-align:right;">{{ number_format($l['perc'], 1, ',', '.') }}%</span>
                        </button>
                    </td>
                    <td style="padding:7px 12px;text-align:right;">
                        <button wire:click="abrirItens({{ $l['fase_padrao_id'] }})" style="{{ $btn }}">Itens</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top:2px solid #9ca3af;background:#f9fafb;font-weight:700;">
                <td style="padding:8px 12px;">TOTAL</td>
                <td style="padding:8px 12px;text-align:right;">{{ $fmt($d['total_orcado']) }}</td>
                <td style="padding:8px 12px;text-align:right;">{{ $fmt($d['total_realizado']) }}</td>
                <td style="padding:8px 12px;text-align:right;color:{{ $corDesvio($d['total_desvio']) }};">{{ $fmt($d['total_desvio']) }}</td>
                <td></td>
                <td style="padding:8px 12px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;">
                            <div style="width:{{ min(100, $d['avanco_fisico']) }}%;background:{{ $corBarra($d['avanco_fisico']) }};height:8px;border-radius:4px;"></div>
                        </div>
                        <span style="font-size:12px;width:48px;text-align:right;">{{ number_format($d['avanco_fisico'], 1, ',', '.') }}%</span>
                    </div>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Modal avanço --}}
@if ($this->faseAvanco)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;" wire:click.self="fecharAvanco">
    <div style="background:#fff;border-radius:12px;padding:24px;width:320px;">
        <h3 style="font-weight:700;margin-bottom:12px;">Avanço da fase</h3>
        <label style="font-size:12px;color:#6b7280;">% executado</label>
        <input type="number" min="0" max="100" step="0.5" wire:model="percentualNovo" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px;margin:6px 0 14px;">
        <div style="display:flex;gap:8px;justify-content:flex-end;">
            <button wire:click="fecharAvanco" style="{{ $btn }}">Cancelar</button>
            <button wire:click="salvarAvanco" style="{{ $btn }}background:#2563eb;color:#fff;border:none;padding:6px 14px;">Salvar</button>
        </div>
    </div>
</div>
@endif

{{-- Modal itens --}}
@if ($this->faseItens)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;" wire:click.self="fecharItens">
    <div style="background:#fff;border-radius:12px;padding:24px;width:min(860px,92vw);max-height:86vh;overflow:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-weight:700;">Itens — {{ $this->nomeFaseItens() }}</h3>
            <button wire:click="fecharItens" style="{{ $btn }}">Fechar</button>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:16px;">
            <thead><tr style="background:#f3f4f6;">
                <th style="padding:6px 8px;text-align:left;">Código</th>
                <th style="padding:6px 8px;text-align:center;">Tipo</th>
                <th style="padding:6px 8px;text-align:left;">Descrição</th>
                <th style="padding:6px 8px;text-align:center;">Unid.</th>
                <th style="padding:6px 8px;text-align:right;">Qtd.</th>
                <th style="padding:6px 8px;text-align:right;">Valor unit.</th>
                <th style="padding:6px 8px;text-align:right;">Total</th>
                <th style="padding:6px 8px;"></th>
            </tr></thead>
            <tbody>
                @foreach ($this->itensDaFase() as $i)
                    <tr style="border-top:1px solid #e5e7eb;">
                        <td style="padding:5px 8px;color:#6b7280;">{{ $i->numero_item }}</td>
                        <td style="padding:5px 8px;text-align:center;">
                            @php $t = $i->tipo ?? 'material'; $lab = ["material"=>"Material","mdo"=>"Mão de obra","outros"=>"Outros"][$t] ?? $t; $cor = ["material"=>"#1d4ed8","mdo"=>"#047857","outros"=>"#b45309"][$t]; $bg = ["material"=>"#dbeafe","mdo"=>"#d1fae5","outros"=>"#fef3c7"][$t]; @endphp
                            <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;background:{{ $bg }};color:{{ $cor }};">{{ $lab }}</span>
                        </td>
                        <td style="padding:5px 8px;">{{ $i->descricao }}</td>
                        <td style="padding:5px 8px;text-align:center;">{{ $i->unidade }}</td>
                        <td style="padding:5px 8px;text-align:right;">{{ number_format((float) $i->quantidade, 2, ',', '.') }}</td>
                        <td style="padding:5px 8px;text-align:right;">{{ number_format((float) $i->valor_unitario, 2, ',', '.') }}</td>
                        <td style="padding:5px 8px;text-align:right;font-weight:600;">{{ $fmt($i->valor_total) }}</td>
                        <td style="padding:5px 8px;text-align:right;white-space:nowrap;">
                            <button wire:click="editarItem({{ $i->id }})" style="{{ $btn }}">Editar</button>
                            <button wire:click="excluirItem({{ $i->id }})" wire:confirm="Excluir este item?" style="{{ $btn }}color:#dc2626;">×</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-top:2px solid #e5e7eb;padding-top:14px;">
            <h4 style="font-weight:600;font-size:13px;margin-bottom:8px;">{{ $this->itemId ? 'Editar item' : 'Adicionar item' }}</h4>
            <div style="display:grid;grid-template-columns:170px 100px 2fr 60px 90px 110px auto;gap:8px;align-items:end;">
                <div><label style="font-size:11px;color:#6b7280;">Serviço (catálogo)</label>
                    <select wire:model="itemServicoId" wire:change="preencherServico" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;background:#fff;">
                        <option value="">— descrição livre —</option>
                        @foreach ($this->servicos() as $s)
                            <option value="{{ $s->id }}">{{ $s->nome }}</option>
                        @endforeach
                    </select></div>
                <div><label style="font-size:11px;color:#6b7280;">Tipo</label>
                    <select wire:model="itemTipo" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;background:#fff;">
                        <option value="material">Material</option>
                        <option value="mdo">Mão de obra</option>
                        <option value="outros">Outros</option>
                    </select></div>
                <div><label style="font-size:11px;color:#6b7280;">Descrição</label>
                    <input wire:model="itemDescricao" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;"></div>
                <div><label style="font-size:11px;color:#6b7280;">Unid.</label>
                    <input wire:model="itemUnidade" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;"></div>
                <div><label style="font-size:11px;color:#6b7280;">Qtd.</label>
                    <input wire:model="itemQuantidade" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;"></div>
                <div><label style="font-size:11px;color:#6b7280;">Valor unit.</label>
                    <input wire:model="itemValor" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:6px;"></div>
                <div style="display:flex;gap:6px;">
                    <button wire:click="salvarItem" style="{{ $btn }}background:#2563eb;color:#fff;border:none;padding:6px 14px;">Salvar</button>
                    @if ($this->itemId)
                        <button wire:click="resetItemForm" style="{{ $btn }}">Cancelar</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
