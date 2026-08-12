@php
    $fmt = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $corDesvio = fn ($v) => $v > 0 ? '#dc2626' : ($v < 0 ? '#16a34a' : '#6b7280');
    $corBarra = fn ($p) => $p >= 80 ? '#22c55e' : ($p >= 50 ? '#eab308' : ($p > 0 ? '#3b82f6' : '#d1d5db'));
    $borda = 'border:1px solid #e5e7eb;border-radius:8px;background:#fff;';
@endphp

{{-- Cards de resumo --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin-bottom:16px;">
    <div style="{{ $borda }}border-left:4px solid #6b7280;padding:12px 16px;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Orçado</div>
        <div style="font-size:18px;font-weight:700;">{{ $fmt($data['total_orcado']) }}</div>
    </div>
    <div style="{{ $borda }}border-left:4px solid #3b82f6;padding:12px 16px;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Realizado</div>
        <div style="font-size:18px;font-weight:700;">{{ $fmt($data['total_realizado']) }}</div>
    </div>
    <div style="{{ $borda }}border-left:4px solid {{ $data['total_desvio'] > 0 ? '#dc2626' : '#16a34a' }};padding:12px 16px;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Desvio</div>
        <div style="font-size:18px;font-weight:700;color:{{ $corDesvio($data['total_desvio']) }};">{{ $fmt($data['total_desvio']) }}</div>
    </div>
    <div style="{{ $borda }}border-left:4px solid #8b5cf6;padding:12px 16px;">
        <div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Avanço Físico</div>
        <div style="font-size:18px;font-weight:700;">{{ number_format($data['avanco_fisico'], 1, ',', '.') }}%</div>
        <div style="margin-top:6px;background:#e5e7eb;border-radius:4px;height:6px;">
            <div style="width:{{ min(100, $data['avanco_fisico']) }}%;background:{{ $corBarra($data['avanco_fisico']) }};height:6px;border-radius:4px;"></div>
        </div>
    </div>
</div>

{{-- Tabela por fase --}}
<div style="{{ $borda }}overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:8px 12px;text-align:left;font-weight:600;">Fase</th>
                <th style="padding:8px 12px;text-align:right;font-weight:600;">Orçado</th>
                <th style="padding:8px 12px;text-align:right;font-weight:600;">Realizado</th>
                <th style="padding:8px 12px;text-align:right;font-weight:600;">Desvio</th>
                <th style="padding:8px 12px;text-align:center;font-weight:600;">Peso</th>
                <th style="padding:8px 12px;text-align:left;font-weight:600;min-width:150px;">Avanço</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['linhas'] as $l)
                <tr style="border-top:1px solid #e5e7eb;">
                    <td style="padding:7px 12px;">
                        <span style="color:#9ca3af;">{{ $l['ordem'] }}.</span> {{ $l['nome'] }}
                    </td>
                    <td style="padding:7px 12px;text-align:right;">{{ $fmt($l['orcado']) }}</td>
                    <td style="padding:7px 12px;text-align:right;">{{ $fmt($l['realizado']) }}</td>
                    <td style="padding:7px 12px;text-align:right;font-weight:600;color:{{ $corDesvio($l['desvio']) }};">
                        {{ $fmt($l['desvio']) }}
                        @if ($l['desvio_pct'] !== null)
                            <span style="font-size:11px;font-weight:400;">({{ $l['desvio_pct'] }}%)</span>
                        @endif
                    </td>
                    <td style="padding:7px 12px;text-align:center;font-size:12px;color:#6b7280;">{{ number_format($l['peso'], 1, ',', '.') }}</td>
                    <td style="padding:7px 12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;">
                                <div style="width:{{ min(100, $l['perc']) }}%;background:{{ $corBarra($l['perc']) }};height:8px;border-radius:4px;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;width:48px;text-align:right;">{{ number_format($l['perc'], 1, ',', '.') }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;font-style:italic;">Sem fases cadastradas.</td></tr>
            @endforelse
        </tbody>
        @if (count($data['linhas']) > 0)
        <tfoot>
            <tr style="border-top:2px solid #9ca3af;background:#f9fafb;font-weight:700;">
                <td style="padding:8px 12px;">TOTAL</td>
                <td style="padding:8px 12px;text-align:right;">{{ $fmt($data['total_orcado']) }}</td>
                <td style="padding:8px 12px;text-align:right;">{{ $fmt($data['total_realizado']) }}</td>
                <td style="padding:8px 12px;text-align:right;color:{{ $corDesvio($data['total_desvio']) }};">{{ $fmt($data['total_desvio']) }}</td>
                <td></td>
                <td style="padding:8px 12px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;background:#e5e7eb;border-radius:4px;height:8px;">
                            <div style="width:{{ min(100, $data['avanco_fisico']) }}%;background:{{ $corBarra($data['avanco_fisico']) }};height:8px;border-radius:4px;"></div>
                        </div>
                        <span style="font-size:12px;width:48px;text-align:right;">{{ number_format($data['avanco_fisico'], 1, ',', '.') }}%</span>
                    </div>
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<p style="margin-top:10px;font-size:11px;color:#6b7280;font-style:italic;">
    Orçado = itens do orçamento da fase · Realizado = pedidos de compra (exceto cancelados) alocados na fase · Avanço = % executado informado em Fases da Obra (média ponderada pelo peso).
</p>
