<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meus Boletos — Equilíbre Construções</title>
    <style>
        body { font-family: system-ui, sans-serif; background:#f3f4f6; margin:0; padding:24px; }
        .card { max-width:640px; margin:0 auto; background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size:20px; margin-top:0; }
        form { display:flex; gap:8px; margin-bottom:24px; }
        input[type=text] { flex:1; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:16px; }
        button { padding:10px 18px; border:0; border-radius:8px; background:#1e293b; color:#fff; font-size:16px; cursor:pointer; }
        .boleto { border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-bottom:12px; }
        .boleto strong { display:block; font-size:15px; }
        .linha { font-family:monospace; background:#f9fafb; padding:8px; border-radius:6px; font-size:13px; word-break:break-all; margin:8px 0; }
        .acoes { display:flex; gap:8px; flex-wrap:wrap; }
        .acoes a, .acoes button { text-decoration:none; font-size:14px; padding:6px 12px; border-radius:6px; }
        .btn-pdf { background:#1e293b; color:#fff; }
        .btn-pix { background:#10b981; color:#fff; border:0; cursor:pointer; }
        .vazio { color:#6b7280; text-align:center; padding:24px 0; }
        .vencido { color:#dc2626; font-weight:600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Consultar Boletos — Equilíbre Construções</h1>
        <form method="GET">
            <input type="text" name="cpf" value="{{ $cpf }}" placeholder="Digite seu CPF" required>
            <button type="submit">Buscar</button>
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
