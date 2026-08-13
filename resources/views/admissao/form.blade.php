<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funcionário</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 16px;
            color: #1f2937;
        }

        .card {
            max-width: 480px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .sub {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin: 14px 0 4px;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .erro {
            color: #dc2626;
            font-size: 13px;
            margin-top: 4px;
        }

        .sucesso {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="card">

    <h1>Cadastro de Funcionário</h1>

    <p class="sub">
        {{ $link->projeto?->nome ?? 'Preencha os dados para admissão' }}
    </p>

    @if (session('sucesso'))
        <div class="sucesso">
            {{ session('sucesso') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admissao.store', $link->token) }}"
          enctype="multipart/form-data">

        @csrf

        <label>Nome completo</label>
        <input
            type="text"
            name="nome"
            value="{{ old('nome') }}"
            required
        >
        @error('nome')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>CPF</label>
        <input
            type="text"
            name="cpf"
            id="cpf"
            value="{{ old('cpf') }}"
            maxlength="14"
            required
        >
        @error('cpf')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Telefone</label>
        <input
            type="text"
            name="telefone"
            id="telefone"
            value="{{ old('telefone') }}"
            maxlength="15"
            required
        >
        @error('telefone')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Função</label>
        <input
            type="text"
            name="funcao"
            value="{{ old('funcao') }}"
            required
        >
        @error('funcao')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Data de entrada</label>
        <input
            type="date"
            name="data_entrada"
            value="{{ old('data_entrada', date('Y-m-d')) }}"
            required
        >
        @error('data_entrada')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Tipo de chave Pix</label>

        <select name="tipo_chave_pix" required>
            <option value="">Selecione</option>
            <option value="cpf">CPF</option>
            <option value="cnpj">CNPJ</option>
            <option value="telefone">Telefone</option>
            <option value="email">E-mail</option>
            <option value="aleatoria">Aleatória</option>
        </select>

        @error('tipo_chave_pix')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Chave Pix</label>

        <input
            type="text"
            name="chave_pix"
            value="{{ old('chave_pix') }}"
            required
        >

        @error('chave_pix')
            <div class="erro">{{ $message }}</div>
        @enderror

        <label>Foto do documento (opcional)</label>

        <input
            type="file"
            name="foto"
            accept="image/*"
            capture="environment"
        >

        @error('foto')
            <div class="erro">{{ $message }}</div>
        @enderror

        <button type="submit">
            Enviar cadastro
        </button>

    </form>

</div>

<script>
document.getElementById('cpf').addEventListener('input', function(e) {
    let v = e.target.value
        .replace(/\D/g, '')
        .slice(0, 11);

    v = v
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    e.target.value = v;
});

document.getElementById('telefone').addEventListener('input', function(e) {
    let v = e.target.value
        .replace(/\D/g, '')
        .slice(0, 11);

    v = v
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d{4})$/, '$1-$2');

    e.target.value = v;
});
</script>

</body>
</html>
