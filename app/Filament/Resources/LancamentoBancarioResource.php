<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Resources\LancamentoBancarioResource\Pages\ListLancamentosBancarios;
use App\Filament\Resources\LancamentoBancarioResource\Pages\CreateLancamentoBancario;
use App\Filament\Resources\LancamentoBancarioResource\Pages\EditLancamentoBancario;
use App\Filament\Resources\LancamentoBancarioResource\Pages;
use App\Models\LancamentoBancario;
use App\Models\ContaBancaria;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class LancamentoBancarioResource extends Resource
{
    protected static ?string $model = LancamentoBancario::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Extrato Bancário';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'extrato-bancario';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
            Select::make('tipo')->label('Tipo')->native(false)->required()->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
            TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
            TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
            DatePicker::make('data')->label('Data')->displayFormat('d/m/Y')->default(now())->required(),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }
    public static function table(Table $table): Table
    {
        $saldosPorContaEData = LancamentoBancario::query()
            ->orderBy('conta_bancaria_id')->orderBy('data')->orderBy('id')
            ->get()
            ->groupBy('conta_bancaria_id')
            ->flatMap(function ($lancamentos, $contaId) {
                $acumulado = (float) (ContaBancaria::find($contaId)?->saldo_inicial ?? 0);
                $porDia = [];
                foreach ($lancamentos as $l) {
                    $acumulado += $l->tipo === 'entrada' ? (float) $l->valor : -(float) $l->valor;
                    $porDia[$contaId.'|'.$l->data->toDateString()] = $acumulado;
                }
                return $porDia;
            })->toArray();
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with('contaBancaria'))
            ->columns([
                TextColumn::make('contaBancaria.nome')->label('Conta')->sortable(),
                TextColumn::make('descricao')->sortable()->label('Descrição')->searchable()->limit(50),
                TextColumn::make('origem')->sortable()->label('Origem')->badge()
                    ->colors(['gray' => 'manual', 'warning' => 'conta_pagar', 'success' => 'conta_receber', 'info' => 'transferencia'])
                    ->formatStateUsing(fn ($state) => match($state) { 'manual' => 'Manual', 'conta_pagar' => 'CP', 'conta_receber' => 'CR', 'transferencia' => 'Transferência', default => $state }),
                TextColumn::make('tipo')->sortable()->label('Tipo')->badge()
                    ->colors(['success' => 'entrada', 'danger' => 'saida'])
                    ->formatStateUsing(fn ($state) => $state === 'entrada' ? '▲ Entrada' : '▼ Saída'),
                Tables\Columns\IconColumn::make('conciliado')->label('Conciliado')->boolean()
                    ->visible(fn ($record) => $record?->origem === 'extrato_inter' || true),
                TextColumn::make('valor')->label('Valor')->money('BRL')->alignEnd()->sortable()->color(fn ($record) => $record->tipo === 'entrada' ? 'success' : 'danger')
                    ->summarize(
                        Summarizer::make()->label('Saldo do dia')
                            ->using(function ($query) use ($saldosPorContaEData) {
                                $first = (clone $query)->first();
                                if (! $first) return null;
                                $dataStr = \Illuminate\Support\Carbon::parse($first->data)->toDateString();
                                $key = $first->conta_bancaria_id.'|'.$dataStr;
                                return $saldosPorContaEData[$key] ?? 0;
                            })
                            ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.'))
                    ),
            ])
            ->groups([
                Group::make('data')->label('Data')
                    ->getTitleFromRecordUsing(fn ($record) => $record->data->format('d/m/Y'))
                    ->orderQueryUsing(fn ($query) => $query->orderBy('data', 'desc')),
            ])
            ->defaultGroup('data')
            ->groupingDirectionSettingHidden()
            ->filters([
                Filter::make('pendentes')->label('Somente pendentes de conciliação')
                    ->query(fn ($query) => $query->where('origem', 'extrato_inter')->where('conciliado', false)),
                SelectFilter::make('tipo')->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
                Filter::make('periodo')->schema([
                    DatePicker::make('data_de')->label('De')->displayFormat('d/m/Y'),
                    DatePicker::make('data_ate')->label('Até')->displayFormat('d/m/Y'),
                ])->query(fn ($query, array $data) => $query->when($data['data_de'], fn ($q, $v) => $q->whereDate('data', '>=', $v))->when($data['data_ate'], fn ($q, $v) => $q->whereDate('data', '<=', $v)))->columns(2),
            ])
            ->recordActions([
                Action::make('conciliar')->label('Conciliar')->icon('heroicon-o-link')->color('warning')
                    ->visible(fn (LancamentoBancario $record) => $record->origem === 'extrato_inter' && ! $record->conciliado)
                    ->fillForm(function (LancamentoBancario $record) {
                        $candidato = null;
                        $destino = null;
                        if ($record->tipo === 'saida') {
                            $candidato = \App\Models\ContaPagar::whereNotIn('status', ['pago', 'cancelado'])
                                ->where('valor', $record->valor)
                                ->whereBetween('data_vencimento', [$record->data->copy()->subDays(5), $record->data->copy()->addDays(5)])
                                ->orderByRaw('ABS(DATEDIFF(data_vencimento, ?))', [$record->data])
                                ->first();
                            if ($candidato) $destino = 'vincular_cp';
                        } elseif ($record->tipo === 'entrada') {
                            $candidato = \App\Models\ContaReceber::whereNotIn('status', ['recebido', 'cancelado'])
                                ->where('valor', $record->valor)
                                ->whereBetween('data_vencimento', [$record->data->copy()->subDays(5), $record->data->copy()->addDays(5)])
                                ->orderByRaw('ABS(DATEDIFF(data_vencimento, ?))', [$record->data])
                                ->first();
                            if ($candidato) $destino = 'vincular_cr';
                        }
                        return [
                            'destino' => $destino,
                            'titulo_id' => $candidato?->id,
                        ];
                    })
                    ->schema(function (LancamentoBancario $record) {
                        $opcoes = ['arquivar' => 'Arquivar sem vincular (tarifa, IOF, etc.)'];
                        if ($record->tipo === 'saida') $opcoes = ['vincular_cp' => 'Vincular a Conta a Pagar existente', 'gerar_cp' => 'Gerar nova Conta a Pagar'] + $opcoes;
                        if ($record->tipo === 'entrada') $opcoes = ['vincular_cr' => 'Vincular a Conta a Receber existente', 'gerar_cr' => 'Gerar nova Conta a Receber'] + $opcoes;
                        return [
                            Select::make('destino')->label('O que fazer com esse lançamento?')->options($opcoes)->required()->live()->native(false),
                            Select::make('titulo_id')->label('Título')->searchable()->native(false)
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) use ($record) {
                                    if ($get('destino') === 'vincular_cp') {
                                        return \App\Models\ContaPagar::whereNotIn('status', ['pago', 'cancelado'])->get()->mapWithKeys(fn ($c) => [$c->id => "#{$c->id} - {$c->descricao} - R$ " . number_format($c->valor, 2, ',', '.')]);
                                    }
                                    if ($get('destino') === 'vincular_cr') {
                                        return \App\Models\ContaReceber::whereNotIn('status', ['recebido', 'cancelado'])->get()->mapWithKeys(fn ($c) => [$c->id => "#{$c->id} - {$c->descricao} - R$ " . number_format($c->valor, 2, ',', '.')]);
                                    }
                                    return [];
                                })
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => in_array($get('destino'), ['vincular_cp', 'vincular_cr'])),
                            Select::make('cliente_id')->label('Cliente')->options(fn () => \App\Models\Cliente::orderBy('nome')->pluck('nome', 'id'))->searchable()->native(false)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('destino') === 'gerar_cr'),
                            Select::make('contato_key')->label('Contato')->options(fn () => \App\Models\Contato::optionsParaSelect())->searchable()->native(false)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('destino') === 'gerar_cp'),
                            Select::make('plano_conta_id')->label('Plano de Conta')->options(fn () => \App\Models\PlanoConta::where('tipo', 'despesa')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('destino') === 'gerar_cp'),
                            Select::make('projeto_id')->label('Empreendimento')->options(fn () => \App\Models\Projeto::pluck('nome', 'id'))->searchable()->native(false)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('destino') === 'gerar_cp'),
                        ];
                    })
                    ->action(function (LancamentoBancario $record, array $data) {
                        if ($data['destino'] === 'vincular_cp') {
                            $conta = \App\Models\ContaPagar::find($data['titulo_id']);
                            $conta?->darBaixa($record->valor, $record->data->toDateString());
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $conta?->id]);
                        } elseif ($data['destino'] === 'vincular_cr') {
                            $conta = \App\Models\ContaReceber::find($data['titulo_id']);
                            $conta?->update(['status' => 'recebido', 'valor_recebido' => $record->valor, 'data_recebimento' => $record->data]);
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $conta?->id]);
                        } elseif ($data['destino'] === 'gerar_cp') {
                            [$contatoTipo, $contatoId] = str_contains($data['contato_key'] ?? '', '|') ? explode('|', $data['contato_key'], 2) : [null, null];
                            $novaConta = \App\Models\ContaPagar::create([
                                'descricao' => $record->descricao,
                                'contato_tipo' => $contatoTipo,
                                'contato_id' => $contatoId,
                                'plano_conta_id' => $data['plano_conta_id'] ?? null,
                                'projeto_id' => $data['projeto_id'] ?? null,
                                'valor' => $record->valor,
                                'valor_pago' => $record->valor,
                                'data_vencimento' => $record->data,
                                'data_pagamento' => $record->data,
                                'status' => 'pago',
                            ]);
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $novaConta->id]);
                        } elseif ($data['destino'] === 'gerar_cr') {
                            $novaConta = \App\Models\ContaReceber::create([
                                'descricao' => $record->descricao,
                                'cliente_id' => $data['cliente_id'] ?? null,
                                'valor' => $record->valor,
                                'valor_recebido' => $record->valor,
                                'data_vencimento' => $record->data,
                                'data_recebimento' => $record->data,
                                'status' => 'recebido',
                            ]);
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $novaConta->id]);
                        } else {
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id()]);
                        }
                        Notification::make()->title('Lançamento conciliado')->success()->send();
                    }),
                EditAction::make()->slideOver()->iconButton()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
                DeleteAction::make()->iconButton()->visible(fn (LancamentoBancario $record) => $record->origem === 'manual'),
            ])
            ->toolbarActions([])->defaultSort('data', 'desc')->dragReorderableColumns()->stickableColumns();
    }
    public static function getPages(): array
    {
        return ['index' => ListLancamentosBancarios::route('/')];
    }
}
