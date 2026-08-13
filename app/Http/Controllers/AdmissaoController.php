<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdmissaoRequest;
use App\Models\AdmissaoLink;
use App\Models\Funcionario;

class AdmissaoController extends Controller
{
    public function show(string $token)
    {
        $link = AdmissaoLink::where('token', $token)->firstOrFail();

        abort_unless(
            $link->valido(),
            410,
            'Link de admissão inválido ou expirado.'
        );

        return view('admissao.form', [
            'link' => $link,
        ]);
    }

    public function store(AdmissaoRequest $request, string $token)
    {
        $link = AdmissaoLink::where('token', $token)->firstOrFail();

        abort_unless(
            $link->valido(),
            410,
            'Link de admissão inválido ou expirado.'
        );

        $data = $request->validated();

        unset($data['foto']);

        $funcionario = Funcionario::create($data + [
            'status' => 'pendente',
            'projeto_id' => $link->projeto_id,
        ]);

        if ($request->hasFile('foto')) {
            $path = $request
                ->file('foto')
                ->store('documentos-funcionarios', 'local');

            $funcionario->update([
                'foto_documento_path' => $path,
            ]);
        }

        return back()->with(
            'sucesso',
            'Cadastro enviado! Aguarde a aprovação.'
        );
    }
}
