<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'cpf' => ['required', 'string', 'max:14', 'unique:funcionarios,cpf'],
            'telefone' => ['required', 'string', 'max:20'],
            'tipo_chave_pix' => ['required', 'in:cpf,cnpj,telefone,email,aleatoria'],
            'chave_pix' => ['required', 'string', 'max:150'],
            'funcao' => ['required', 'string', 'max:100'],
            'data_entrada' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'Este CPF já está cadastrado.',
        ];
    }
}
