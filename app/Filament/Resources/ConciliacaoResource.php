<?php
namespace App\Filament\Resources;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Resources\ConciliacaoResource\Pages\ListConciliacoes;
use App\Models\LancamentoBancario;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class ConciliacaoResource extends Resource
{
    protected static ?string $model = LancamentoBancario::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Conciliação';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'conciliacao';
    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with('contaBancaria')->where('origem', 'extrato_inter')->where('conciliado', false))
            ->columns([
                TextColumn::make('data')->label('Data')->date('d/m/Y')->sortable(),
                TextColumn::make('contaBancaria.nome')->label('Conta')->sortable(),
                TextColumn::make('descricao')->label('Descrição')->searchable()->limit(50),
                TextColumn::make('tipo')->label('Tipo')->badge()
                    ->colors(['success' => 'entrada', 'danger' => 'saida'])
                    ->formatStateUsing(fn ($state) => $state === 'entrada' ? '▲ Entrada' : '▼ Saída'),
                TextColumn::make('valor')->label('Valor')->money('BRL')->alignEnd()->sortable()
                    ->color(fn ($record) => $record->tipo === 'entrada' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('tipo')->options(['entrada' => 'Entrada', 'saida' => 'Saída']),
                Filter::make('periodo')->schema([
                    DatePicker::make('data_de')->label('De')->displayFormat('d/m/Y'),
                    DatePicker::make('data_ate')->label('Até')->displayFormat('d/m/Y'),
                ])->query(fn ($query, array $data) => $query->when($data['data_de'], fn ($q, $v) => $q->whereDate('data', '>=', $v))->when($data['data_ate'], fn ($q, $v) => $q->whereDate('data', '<=', $v)))->columns(2),
            ])
            ->recordActions([
                Action::make('conciliar')->label('Conciliar')->icon('heroicon-o-link')->color('warning')
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
                        $opcoes = [];
                        if ($record->tipo === 'saida') $opcoes = ['vincular_cp' => 'Vincular a Conta a Pagar existente', 'gerar_cp' => 'Gerar nova Conta a Pagar'] + $opcoes;
                        if ($record->tipo === 'entrada') $opcoes = ['vincular_cr' => 'Vincular a Conta a Receber existente', 'gerar_cr' => 'Gerar nova Conta a Receber'] + $opcoes;
                        return [
                            \Filament\Forms\Components\Radio::make('destino')->label('O que fazer com esse lançamento?')->options($opcoes)->required()->live()->inline()->inlineLabel(false),
                            Select::make('titulo_id')->label('Título')->searchable()->native(false)
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) use ($record) {
                                    if ($get('destino') === 'vincular_cp') {
                                        return \App\Models\ContaPagar::whereNotIn('status', ['pago', 'cancelado'])->get()->mapWithKeys(fn ($c) => [$c->id => "#{$c->id} - {$c->descricao} - {$c->nome_contato} - Venc: " . $c->data_vencimento->format('d/m/Y') . " - R$ " . number_format($c->valor, 2, ',', '.')]);
                                    }
                                    if ($get('destino') === 'vincular_cr') {
                                        return \App\Models\ContaReceber::whereNotIn('status', ['recebido', 'cancelado'])->with('cliente')->get()->mapWithKeys(fn ($c) => [$c->id => "#{$c->id} - {$c->descricao} - " . ($c->cliente->nome ?? 'sem cliente') . " - Venc: " . $c->data_vencimento->format('d/m/Y') . " - R$ " . number_format($c->valor, 2, ',', '.')]);
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
                            Select::make('fase_padrao_id')->label('Fase')->options(fn () => \App\Models\FasePadrao::orderBy('ordem')->pluck('nome', 'id'))->searchable()->native(false)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('destino') === 'gerar_cp'),
                        ];
                    })
                    ->action(function (LancamentoBancario $record, array $data) {
                        if ($data['destino'] === 'vincular_cp') {
                            $conta = \App\Models\ContaPagar::find($data['titulo_id']);
                            \App\Models\ContaPagar::withoutEvents(fn () => $conta?->darBaixa($record->valor, $record->data->toDateString()));
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $conta?->id]);
                        } elseif ($data['destino'] === 'vincular_cr') {
                            $conta = \App\Models\ContaReceber::find($data['titulo_id']);
                            \App\Models\ContaReceber::withoutEvents(fn () => $conta?->update(['status' => 'recebido', 'valor_recebido' => $record->valor, 'data_recebimento' => $record->data]));
                            $record->update(['conciliado' => true, 'conciliado_em' => now(), 'conciliado_por' => auth()->id(), 'origem_id' => $conta?->id]);
                        } elseif ($data['destino'] === 'gerar_cp') {
                            [$contatoTipo, $contatoId] = str_contains($data['contato_key'] ?? '', '|') ? explode('|', $data['contato_key'], 2) : [null, null];
                            $novaConta = \App\Models\ContaPagar::create([
                                'descricao' => $record->descricao,
                                'contato_tipo' => $contatoTipo,
                                'contato_id' => $contatoId,
                                'plano_conta_id' => $data['plano_conta_id'] ?? null,
                                'projeto_id' => $data['projeto_id'] ?? null,
                                'fase_padrao_id' => $data['fase_padrao_id'] ?? null,
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
                        }
                        Notification::make()->title('Lançamento conciliado')->success()->send();
                    }),
            ])
            ->defaultSort('data', 'desc');
    }
    public static function getPages(): array
    {
        return ['index' => ListConciliacoes::route('/')];
    }
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('origem', 'extrato_inter')->where('conciliado', false)->count();
    }
}
