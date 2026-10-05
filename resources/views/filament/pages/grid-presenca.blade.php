<x-filament-panels::page>
    <div style="display:flex;gap:16px;align-items:end;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Obra</label>
            <select wire:model.live="projetoId" style="border:1px solid rgba(128,128,128,.35);border-radius:8px;padding:6px 10px;">
                <option value="">Todas</option>
                @foreach ($this->projetos as $id => $nome)
                    <option value="{{ $id }}">{{ $nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Início</label>
            <input type="date" wire:model.live="periodoInicio" style="border:1px solid rgba(128,128,128,.35);border-radius:8px;padding:6px 10px;">
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Fim</label>
            <input type="date" wire:model.live="periodoFim" style="border:1px solid rgba(128,128,128,.35);border-radius:8px;padding:6px 10px;">
        </div>
        <button
            wire:click="fecharQuinzena"
            wire:confirm="Fechar quinzena e gerar Contas a Pagar para os funcionários pendentes?"
            style="margin-left:auto;background:#16a34a;color:#fff;font-weight:600;font-size:13px;padding:8px 16px;border-radius:8px;">
            Fechar Quinzena
        </button>
    </div>

    <div style="overflow-x:auto;border:1px solid rgba(128,128,128,.25);border-radius:12px;">
        <table style="border-collapse:collapse;width:100%;font-size:13px;">
            <thead>
                <tr style="background:rgba(128,128,128,.08);">
                    <th style="position:sticky;left:0;background:inherit;text-align:left;padding:8px 12px;white-space:nowrap;">Funcionário</th>
                    @foreach ($this->dias as $dia)
                        <th style="padding:6px 4px;text-align:center;white-space:nowrap;font-weight:500;">
                            {{ \Carbon\Carbon::parse($dia)->format('d/m') }}<br>
                            <span style="font-size:10px;opacity:.6;">{{ \Carbon\Carbon::parse($dia)->translatedFormat('D') }}</span>
                        </th>
                    @endforeach
                    <th style="padding:6px 8px;text-align:center;">Dias</th>
                    <th style="padding:6px 8px;text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $fracaoMap = ['integral' => 1, 'falta' => 0, 'meia_manha' => 0.5, 'meia_tarde' => 0.5]; @endphp
                @forelse ($this->funcionarios as $funcionario)
                    @php
                        $totalDias = collect($this->presencas[$funcionario->id] ?? [])->sum(fn ($s) => $fracaoMap[$s] ?? 0);
                        $fechado = isset($this->fechamentos[$funcionario->id]);
                    @endphp
                    <tr style="border-top:1px solid rgba(128,128,128,.15);">
                        <td style="position:sticky;left:0;background:var(--fi-panel-page-bg,#fff);padding:8px 12px;white-space:nowrap;font-weight:500;">
                            {{ $funcionario->nome }}
                        </td>
                        @foreach ($this->dias as $dia)
                            @php
                                $status = $this->presencas[$funcionario->id][$dia] ?? null;
                                [$label, $cor] = match ($status) {
                                    'integral' => ['P', '#16a34a'],
                                    'falta' => ['F', '#dc2626'],
                                    'meia_manha' => ['½M', '#f59e0b'],
                                    'meia_tarde' => ['½T', '#0ea5e9'],
                                    default => ['', 'rgba(128,128,128,.12)'],
                                };
                            @endphp
                            <td style="text-align:center;padding:2px;">
                                <button
                                    wire:click="toggle({{ $funcionario->id }}, '{{ $dia }}')"
                                    @disabled($fechado)
                                    title="Clique pra alternar: Integral → Falta → Meia Manhã → Meia Tarde → vazio"
                                    style="width:30px;height:26px;border-radius:6px;font-weight:700;font-size:10px;
                                        background:{{ $cor }};
                                        color:{{ $status === null ? 'inherit' : '#fff' }};">
                                    {{ $label }}
                                </button>
                            </td>
                        @endforeach
                        <td style="text-align:center;font-weight:600;">{{ rtrim(rtrim(number_format($totalDias, 1, ',', ''), '0'), ',') }}</td>
                        <td style="text-align:center;">
                            @if ($fechado)
                                <span style="background:rgba(22,163,74,.12);color:#16a34a;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;">Fechado</span>
                            @else
                                <span style="background:rgba(128,128,128,.12);padding:2px 8px;border-radius:6px;font-size:11px;">Aberto</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="100%" style="padding:16px;text-align:center;opacity:.6;">Nenhum funcionário ativo encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p style="margin-top:8px;font-size:12px;opacity:.6;">Clique no dia pra alternar: Integral (P) → Falta (F) → Meia Manhã (½M) → Meia Tarde (½T) → vazio.</p>
</x-filament-panels::page>
