<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meus Boletos — Equilíbre Construções</title>
    <style>
        * { box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background:#eef2f7;
            margin:0;
            padding:40px 16px;
        }
        .card {
            max-width:480px;
            margin:0 auto;
            background:#fff;
            border-radius:16px;
            padding:32px 28px;
            box-shadow:0 8px 24px rgba(15,23,42,.08);
        }
        h1 {
            font-size:15px;
            font-weight:700;
            color:#7c2d12;
            margin:0 0 16px;
        }
        form { display:flex; flex-direction:column; gap:16px; margin-bottom:8px; }
        .input-wrap { position:relative; }
        .input-wrap svg {
            position:absolute; left:14px; top:50%; transform:translateY(-50%);
            width:16px; height:16px; color:#9ca3af; pointer-events:none;
        }
        input[type=text] {
            width:100%;
            padding:14px 14px 14px 40px;
            border:1px solid #e2e8f0;
            border-radius:10px;
            font-size:15px;
            background:#fff;
            color:#1e293b;
        }
        input[type=text]:focus { outline:none; border-color:#1e3a8a; }
        button {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:14px 18px;
            border:0;
            border-radius:999px;
            background:linear-gradient(90deg,#0f172a 0%,#1d4ed8 100%);
            color:#fff;
            font-size:15px;
            font-weight:600;
            cursor:pointer;
            box-shadow:0 6px 16px rgba(29,78,216,.35);
        }
        button svg { width:16px; height:16px; }

        .boleto { border:1px solid #e5e7eb; border-radius:12px; padding:16px; margin-top:16px; }
        .boleto strong { display:block; font-size:15px; color:#1e293b; }
        .linha { font-family:monospace; background:#f9fafb; padding:8px; border-radius:6px; font-size:13px; word-break:break-all; margin:8px 0; }
        .acoes { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        .acoes a, .acoes button { text-decoration:none; font-size:14px; padding:8px 14px; border-radius:8px; box-shadow:none; }
        .btn-pdf { background:#0f172a; color:#fff; }
        .btn-pix { background:#10b981; color:#fff; border:0; cursor:pointer; }
        .vazio { color:#6b7280; text-align:center; padding:24px 0; }
        .vencido { color:#dc2626; font-weight:600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Digite seu CPF para consultar os boletos</h1>
        <form method="GET">
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17a2 2 0 002-2 2 2 0 00-2-2 2 2 0 00-2 2 2 2 0 002 2zm6-9a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V10a2 2 0 012-2h1V6a5 5 0 0110 0v2h1zm-6-4a3 3 0 00-3 3v2h6V6a3 3 0 00-3-3z"/></svg>
                <input type="text" name="cpf" value="{{ $cpf }}" placeholder="000.000.000-00" required>
            </div>
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Consultar Boletos
            </button>
        </form>

        @if($buscou)
            @forelse($contas as $conta)
                <div class="boleto">
                    <strong>{{ $conta->descricao }}</strong>
                    <span>Valor: R$ {{ number_format($conta->valor, 2, ',', '.') }}</span><br>
                    <span class="{{ $conta->status === 'vencido' ? 'vencido' : '' }}">
                        Vencimento: {{ $conta->data_vencimento->format('d/m/Y') }}
                        @if($conta->status === 'vencido') (VENCIDO) @endif
                    </span>
                    @if($conta->inter_linha_digitavel)
                        <div class="linha">{{ $conta->inter_linha_digitavel }}</div>
                    @endif
                    <div class="acoes">
                        <a class="btn-pdf" href="{{ route('boleto.pdf.publico', $conta->inter_codigo_solicitacao) }}" target="_blank">Ver PDF</a>
                        @if($conta->inter_linha_digitavel)
                            <button class="btn-pix" style="background:#1e293b" onclick="navigator.clipboard.writeText('{{ $conta->inter_linha_digitavel }}'); this.textContent='Copiado!'">Copiar código de barras</button>
                        @endif
                        @if($conta->inter_pix_copia_cola)
                            <button class="btn-pix" onclick="navigator.clipboard.writeText('{{ $conta->inter_pix_copia_cola }}'); this.textContent='Copiado!'">Copiar PIX</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="vazio">Nenhum boleto em aberto encontrado para este CPF.</p>
            @endforelse
        @endif
    </div>
</body>
</html>
