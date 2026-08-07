<?php

namespace App\Filament\Imports;

use App\Models\Cliente;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ClienteImporter extends Importer
{
    protected static ?string $model = Cliente::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nome')
                ->requiredMapping()
                ->rules(['required', 'max:150']),

            ImportColumn::make('cpf')
                ->rules(['nullable', 'max:14'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    if ($state) {
                        $cpf = preg_replace('/[^0-9]/', '', $state);
                        if (self::validarCpf($cpf)) {
                            $record->cpf = $cpf;
                        } else {
                            throw new \Exception("CPF inválido: {$state}");
                        }
                    }
                }),

            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:100']),

            ImportColumn::make('telefone')
                ->rules(['nullable', 'max:20'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    $record->telefone = $state ? preg_replace('/[^0-9+()\- ]/', '', $state) : null;
                }),

            ImportColumn::make('whatsapp')
                ->rules(['nullable', 'max:20'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    $record->whatsapp = $state ? preg_replace('/[^0-9+()\- ]/', '', $state) : null;
                }),

            ImportColumn::make('profissao')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('renda_familiar')
                ->numeric()
                ->rules(['nullable', 'numeric'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    if ($state) {
                        // Aceita vírgula ou ponto como separador decimal
                        $valor = str_replace(',', '.', str_replace('.', '', $state));
                        $record->renda_familiar = is_numeric($valor) ? (float) $valor : null;
                    }
                }),

            ImportColumn::make('estado_civil')
                ->rules(['nullable', 'in:solteiro,casado,divorciado,viuvo,uniao_estavel']),

            ImportColumn::make('conjuge_nome')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('conjuge_cpf')
                ->rules(['nullable', 'max:14'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    if ($state) {
                        $cpf = preg_replace('/[^0-9]/', '', $state);
                        if (self::validarCpf($cpf)) {
                            $record->conjuge_cpf = $cpf;
                        } else {
                            throw new \Exception("CPF do cônjuge inválido: {$state}");
                        }
                    }
                }),

            ImportColumn::make('conjuge_profissao')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('conjuge_email')
                ->rules(['nullable', 'email', 'max:100']),

            ImportColumn::make('conjuge_telefone')
                ->rules(['nullable', 'max:20'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    $record->conjuge_telefone = $state ? preg_replace('/[^0-9+()\- ]/', '', $state) : null;
                }),

            ImportColumn::make('conjuge_renda')
                ->numeric()
                ->rules(['nullable', 'numeric'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    if ($state) {
                        $valor = str_replace(',', '.', str_replace('.', '', $state));
                        $record->conjuge_renda = is_numeric($valor) ? (float) $valor : null;
                    }
                }),

            ImportColumn::make('cep')
                ->rules(['nullable', 'max:9'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    $record->cep = $state ? preg_replace('/[^0-9]/', '', $state) : null;
                }),

            ImportColumn::make('logradouro')
                ->rules(['nullable', 'max:150']),

            ImportColumn::make('numero')
                ->rules(['nullable', 'max:20']),

            ImportColumn::make('complemento')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('bairro')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('cidade')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('estado')
                ->rules(['nullable', 'max:2'])
                ->fillRecordUsing(function (Cliente $record, ?string $state): void {
                    $record->estado = $state ? strtoupper($state) : null;
                }),

            ImportColumn::make('observacoes')
                ->rules(['nullable']),

            ImportColumn::make('ativo')
                ->boolean()
                ->rules(['nullable']),
        ];
    }

    public function resolveRecord(): Cliente
    {
        if (! empty($this->data['cpf'])) {
            $cpf = preg_replace('/[^0-9]/', '', $this->data['cpf']);
            return Cliente::firstOrNew(['cpf' => $cpf]);
        }
        return new Cliente();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de clientes concluída: '.number_format($import->successful_rows).' de '.number_format($import->total_rows).' linha(s) importada(s) com sucesso.';
        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failed).' linha(s) falharam.';
        }
        return $body;
    }

    /**
     * Valida CPF usando algoritmo de dígito verificador
     */
    private static function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;

        if ((int) $cpf[9] !== $digito1) {
            return false;
        }

        // Calcula segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;

        return (int) $cpf[10] === $digito2;
    }
}
