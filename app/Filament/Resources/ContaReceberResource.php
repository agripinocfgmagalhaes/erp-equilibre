<?php
namespace App\Filament\Resources;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContaReceberResource\Pages\ListContasReceber;
use App\Filament\Resources\ContaReceberResource\Pages\CreateContaReceber;
use App\Filament\Resources\ContaReceberResource\Pages\EditContaReceber;
use App\Filament\Resources\ContaReceberResource\Pages;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\PlanoConta;
use App\Models\ContaBancaria;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
class ContaReceberResource extends Resource
{
    protected static ?string $model = ContaReceber::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Contas a Receber';
    protected static string | \UnitEnum | null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'contas-receber';
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do Título')->schema([
                TextInput::make('descricao')->label('Descrição')->required()->maxLength(200)->columnSpanFull(),
                TextInput::make('numero_documento')->label('Nº Documento')->maxLength(50),
                Select::make('cliente_id')->label('Cliente')->options(Cliente::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('plano_conta_id')->label('Plano de Conta')->options(PlanoConta::where('tipo', 'receita')->where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Select::make('projeto_id')->label('Empreendimento')->options(Projeto::pluck('nome', 'id'))->searchable()->native(false)->nullable(),
                Toggle::make('cancelado')->label('Cancelado')->default(false)
                    ->afterStateHydrated(fn ($component, $record) => $component->state($record?->status === 'cancelado'))
                    ->dehydrated(false)->live()->afterStateUpdated(fn (callable $set, $state) => $set('status', $state ? 'cancelado' : 'aberto')),
                Hidden::make('status')->default('aberto'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Valores e Datas')->schema([
                TextInput::make('valor')->label('Valor')->numeric()->prefix('R$')->step(0.01)->required(),
                TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->default(0)->readOnly(),
                DatePicker::make('data_vencimento')->label('Vencimento')->displayFormat('d/m/Y')->required(),
                DatePicker::make('data_recebimento')->label('Data de Recebimento')->displayFormat('d/m/Y')->readOnly(),
                Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
    public static function table(Table $table): Table
    {
        /** @var \Hydrat\TableLayoutToggle\Concerns\HasToggleableTable $livewire */
        $livewire = $table->getLivewire();
        return $table
        ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['projeto', 'planoConta', 'cliente', 'contaBancaria']))
        ->columns($livewire->isGridLayout() ? static::getGridTableColumns() : static::getListTableColumns())
        ->contentGrid(fn () => $livewire->isListLayout() ? null : ['md' => 2, 'lg' => 3])
        ->filters([
            SelectFilter::make('status')->options(['aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado']),
            SelectFilter::make('projeto_id')->label('Empreendimento')->relationship('projeto', 'nome')->searchable()->preload(),
            SelectFilter::make('cliente_id')->label('Cliente')->relationship('cliente', 'nome')->searchable()->preload(),
            SelectFilter::make('plano_conta_id')->label('Plano de Conta')->relationship('planoConta', 'nome')->searchable()->preload(),
            SelectFilter::make('conta_bancaria_id')->label('Conta Bancária')->relationship('contaBancaria', 'nome')->searchable()->preload(),
            Filter::make('data_vencimento')
                ->form([
                    DatePicker::make('de')->label('Vencimento de'),
                    DatePicker::make('ate')->label('Vencimento até'),
                ])
                ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $query
                    ->when($data['de'] ?? null, fn ($q, $date) => $q->whereDate('data_vencimento', '>=', $date))
                    ->when($data['ate'] ?? null, fn ($q, $date) => $q->whereDate('data_vencimento', '<=', $date))
                ),
            Filter::make('inter_emitido_em')
                ->form([
                    DatePicker::make('de')->label('Boleto emitido de'),
                    DatePicker::make('ate')->label('Boleto emitido até'),
                ])
                ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $query
                    ->when($data['de'] ?? null, fn ($q, $date) => $q->whereDate('inter_emitido_em', '>=', $date))
                    ->when($data['ate'] ?? null, fn ($q, $date) => $q->whereDate('inter_emitido_em', '<=', $date))
                ),
            SelectFilter::make('tem_boleto')->label('Boleto')
                ->options(['sim' => 'Com boleto emitido', 'nao' => 'Sem boleto'])
                ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $query
                    ->when(($data['value'] ?? null) === 'sim', fn ($q) => $q->whereNotNull('inter_codigo_solicitacao'))
                    ->when(($data['value'] ?? null) === 'nao', fn ($q) => $q->whereNull('inter_codigo_solicitacao'))
                ),
        ])
        ->filtersFormColumns(2)
        ->recordActions([
Action::make('emitirBoleto')->label('Emitir Boleto Inter')->icon('heroicon-o-document-currency-dollar')->color('info')->iconButton()
                ->visible(fn (ContaReceber $record) => !$record->inter_codigo_solicitacao && $record->status !== 'recebido')
                ->action(function (ContaReceber $record) {
                    try {
                        app(\App\Services\InterBoletoService::class)->emitir($record);
                        Notification::make()->title('Boleto emitido')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Falha ao emitir boleto')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('verBoleto')->label('Ver Boleto')->icon('heroicon-o-eye')->color('gray')->iconButton()
                ->visible(fn (ContaReceber $record) => (bool) $record->inter_codigo_solicitacao)
                ->modalContent(fn (ContaReceber $record) => view('boleto-info', ['conta' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            \Filament\Actions\ActionGroup::make([
            Action::make('darBaixa')->label('Dar Baixa')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (ContaReceber $record) => ! in_array($record->status, ['recebido', 'cancelado']))
                ->schema(fn (ContaReceber $record) => [
                    TextInput::make('valor_recebido')->label('Valor Recebido')->numeric()->prefix('R$')->step(0.01)->minValue(0.01)->required()->live(onBlur: true)
                        ->helperText('Acima do saldo, a diferença é registrada como juros/multa.'),
                    \Filament\Forms\Components\Placeholder::make('info_baixa')->label('')
                        ->content(function (\Filament\Schemas\Components\Utilities\Get $get) use ($record) {
                            $saldo = round((float) $record->valor - (float) $record->valor_recebido, 2);
                            $dif = round((float) $get('valor_recebido') - $saldo, 2);
                            $f = fn ($n) => 'R$ ' . number_format($n, 2, ',', '.');
                            if ($dif > 0.01) return 'Saldo ' . $f($saldo) . ' + ' . $f($dif) . ' de juros/multa';
                            if ($dif < -0.01) return 'Baixa parcial: restará ' . $f(-$dif) . ' em aberto';
                            return 'Quitação exata do saldo de ' . $f($saldo);
                        }),
                    DatePicker::make('data_recebimento')->label('Data do Recebimento')->displayFormat('d/m/Y')->default(now())->required(),
                    Select::make('conta_bancaria_id')->label('Conta Bancária')->options(ContaBancaria::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
                ])
                ->fillForm(fn (ContaReceber $record) => ['valor_recebido' => round((float) $record->valor - (float) $record->valor_recebido, 2)])
                ->action(function (ContaReceber $record, array $data) {
                    try {
                        $r = app(\App\Services\Financeiro\ContaReceberService::class)->darBaixa($record, (float) $data['valor_recebido'], $data['data_recebimento'], $data['conta_bancaria_id'] ?? null, true);
                        $j = max(0, round((float) $r->valor_recebido - (float) $r->valor, 2));
                        Notification::make()->title('Baixa realizada')->body($j > 0 ? 'Juros/multa: R$ ' . number_format($j, 2, ',', '.') : null)->success()->send();
                    } catch (\Throwable $e) {
                        report($e);
                        Notification::make()->title('Falha ao dar baixa')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('reemitirBoleto')->label('Cancelar e Reemitir Boleto')->icon('heroicon-o-arrow-path')->color('warning')
                ->visible(fn (ContaReceber $record) => $record->inter_codigo_solicitacao && ! in_array($record->status, ['recebido', 'cancelado']))
                ->modalDescription('O boleto atual será cancelado no Inter e um novo será emitido com os dados abaixo.')
                ->schema([
                    TextInput::make('valor')->label('Novo valor')->numeric()->prefix('R$')->step(0.01)->minValue(0.01)->required(),
                    DatePicker::make('data_vencimento')->label('Novo vencimento')->displayFormat('d/m/Y')->required(),
                ])
                ->fillForm(fn (ContaReceber $record) => ['valor' => $record->valor, 'data_vencimento' => $record->data_vencimento])
                ->action(function (ContaReceber $record, array $data) {
                    try {
                        app(\App\Services\InterBoletoService::class)->reemitir($record, ['valor' => $data['valor'], 'data_vencimento' => $data['data_vencimento']]);
                        Notification::make()->title('Boleto cancelado e reemitido')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Falha ao reemitir boleto')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('cancelarBoleto')->label('Cancelar Boleto')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (ContaReceber $record) => $record->inter_codigo_solicitacao && ! in_array($record->status, ['recebido', 'cancelado']))
                ->modalHeading('Cancelar boleto no Inter')
                ->modalDescription('O boleto será cancelado no Inter e o título ficará sem boleto, podendo emitir um novo depois.')
                ->schema([
                    Select::make('motivo')->label('Motivo')->options(['ACERTOS' => 'Acertos', 'APEDIDODOCLIENTE' => 'A pedido do cliente', 'PAGODIRETOAOCREDOR' => 'Pago direto ao credor', 'SUBSTITUICAO' => 'Substituição'])->default('ACERTOS')->native(false)->required(),
                ])
                ->action(function (ContaReceber $record, array $data) {
                    try {
                        app(\App\Services\InterBoletoService::class)->cancelarBoleto($record, $data['motivo']);
                        Notification::make()->title('Boleto cancelado')->success()->send();
                    } catch (\Throwable $e) {
                        report($e);
                        Notification::make()->title('Falha ao cancelar boleto')->body($e->getMessage())->danger()->send();
                    }
                }),
            EditAction::make()->slideOver()->modalWidth('4xl'),
            DeleteAction::make(),
        ])->icon('heroicon-m-ellipsis-vertical')->tooltip('Ações')
        ])
        ->toolbarActions([BulkActionGroup::make([
            \Filament\Actions\BulkAction::make('alterarValorLote')->label('Alterar Valor em Lote')->icon('heroicon-o-currency-dollar')->color('warning')
                ->schema([
                    Select::make('modo')->label('Modo')->options(['fixo' => 'Definir valor fixo', 'percentual' => 'Ajuste percentual (%)', 'soma' => 'Somar/subtrair valor (R$)'])->default('fixo')->native(false)->required(),
                    TextInput::make('valor')->label('Valor / %')->numeric()->step(0.01)->required()->helperText('Para reduzir use número negativo (modos % e R$).'),
                    Toggle::make('reemitir')->label('Cancelar e reemitir boletos já emitidos')->default(false),
                ])
                ->modalDescription('Títulos recebidos ou cancelados são ignorados. Com boleto emitido: só se a opção de reemitir estiver ligada.')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records, array $data) {
                    $v = (float) $data['valor'];
                    $ok = 0; $ign = 0; $falha = 0;
                    foreach ($records as $r) {
                        if (in_array($r->status, ['recebido', 'cancelado']) || ($r->inter_codigo_solicitacao && ! ($data['reemitir'] ?? false))) { $ign++; continue; }
                        $novo = round(match ($data['modo']) {
                            'percentual' => (float) $r->valor * (1 + $v / 100),
                            'soma' => (float) $r->valor + $v,
                            default => $v,
                        }, 2);
                        if ($novo <= 0) { $ign++; continue; }
                        if ($r->inter_codigo_solicitacao) {
                            try { app(\App\Services\InterBoletoService::class)->reemitir($r, ['valor' => $novo]); }
                            catch (\Throwable $e) { $falha++; continue; }
                        } else {
                            $r->update(['valor' => $novo]);
                        }
                        $ok++;
                    }
                    Notification::make()->title($ok . ' atualizado(s), ' . $ign . ' ignorado(s)' . ($falha ? ', ' . $falha . ' falha(s) ao reemitir (conferir no Inter)' : ''))->success()->send();
                }),
            \Filament\Actions\BulkAction::make('alterarDiaLote')->label('Alterar Dia do Vencimento')->icon('heroicon-o-calendar-days')->color('warning')
                ->schema([
                    TextInput::make('dia')->label('Novo dia')->numeric()->minValue(1)->maxValue(31)->required()->helperText('Mês e ano são mantidos. Em meses mais curtos usa o último dia.'),
                    Toggle::make('reemitir')->label('Cancelar e reemitir boletos já emitidos')->default(false),
                ])
                ->modalDescription('Títulos recebidos ou cancelados são ignorados. Com boleto emitido: só se a opção de reemitir estiver ligada.')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records, array $data) {
                    $ok = 0; $ign = 0; $falha = 0;
                    foreach ($records as $r) {
                        if (in_array($r->status, ['recebido', 'cancelado']) || ($r->inter_codigo_solicitacao && ! ($data['reemitir'] ?? false))) { $ign++; continue; }
                        $dv = \Carbon\Carbon::parse($r->data_vencimento);
                        $novo = $dv->copy()->day(min((int) $data['dia'], $dv->daysInMonth))->toDateString();
                        if ($novo === $dv->toDateString()) { $ign++; continue; }
                        if ($r->inter_codigo_solicitacao) {
                            try { app(\App\Services\InterBoletoService::class)->reemitir($r, ['data_vencimento' => $novo]); }
                            catch (\Throwable $e) { $falha++; continue; }
                        } else {
                            $r->update(['data_vencimento' => $novo]);
                        }
                        $ok++;
                    }
                    Notification::make()->title($ok . ' atualizado(s), ' . $ign . ' ignorado(s)' . ($falha ? ', ' . $falha . ' falha(s) ao reemitir (conferir no Inter)' : ''))->success()->send();
                }),
            \Filament\Actions\BulkAction::make('emitirBoletosLote')->label('Emitir Boletos Inter')->icon('heroicon-o-document-currency-dollar')->color('info')
                ->requiresConfirmation()
                ->modalDescription('Emite boleto para os títulos selecionados que ainda não têm boleto. Recebidos, cancelados, já emitidos e clientes sem CPF/CNPJ são ignorados.')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records) {
                    set_time_limit(300);
                    $svc = app(\App\Services\InterBoletoService::class);
                    $ok = 0; $ign = 0; $erros = [];
                    foreach ($records as $r) {
                        if (in_array($r->status, ['recebido', 'cancelado']) || $r->inter_codigo_solicitacao || empty($r->cliente?->cpf)) { $ign++; continue; }
                        try { $svc->emitir($r); $ok++; usleep(300000); }
                        catch (\Throwable $e) { $erros[] = '#' . $r->id . ': ' . mb_substr($e->getMessage(), 0, 120); }
                    }
                    $n = Notification::make()->title($ok . ' emitido(s), ' . $ign . ' ignorado(s)' . ($erros ? ', ' . count($erros) . ' erro(s)' : ''));
                    if ($erros) { $n->body(implode("\n", array_slice($erros, 0, 5)))->warning(); } else { $n->success(); }
                    $n->send();
                }),
            \Filament\Actions\BulkAction::make('alterarMesLote')->label('Alterar Mês do Vencimento')->icon('heroicon-o-calendar')->color('warning')
                ->schema([
                    Select::make('modo')->label('Modo')->options(['deslocar' => 'Deslocar meses (+/-)', 'definir' => 'Definir mês'])->default('deslocar')->native(false)->live()->required(),
                    TextInput::make('meses')->label('Quantidade de meses')->numeric()->integer()->step(1)->minValue(-60)->maxValue(60)
                        ->helperText('Positivo adia, negativo antecipa. Ex.: 1 = próximo mês; -1 = mês anterior.')
                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('modo') === 'deslocar')
                        ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('modo') === 'deslocar'),
                    Select::make('mes')->label('Novo mês')->native(false)
                        ->options([1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'])
                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('modo') === 'definir')
                        ->required(fn (\Filament\Schemas\Components\Utilities\Get $get) => $get('modo') === 'definir'),
                    Toggle::make('reemitir')->label('Cancelar e reemitir boletos já emitidos')->default(false),
                ])
                ->modalDescription('Dia e ano são mantidos (no modo "definir mês"). Em meses mais curtos usa o último dia. Títulos recebidos ou cancelados são ignorados; com boleto emitido, só se a opção de reemitir estiver ligada.')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records, array $data) {
                    $ok = 0; $ign = 0; $falha = 0;
                    foreach ($records as $r) {
                        if (in_array($r->status, ['recebido', 'cancelado']) || ($r->inter_codigo_solicitacao && ! ($data['reemitir'] ?? false))) { $ign++; continue; }
                        $dv = \Carbon\Carbon::parse($r->data_vencimento);
                        if (($data['modo'] ?? 'deslocar') === 'deslocar') {
                            $novo = $dv->copy()->addMonthsNoOverflow((int) $data['meses'])->toDateString();
                        } else {
                            $base = \Carbon\Carbon::create($dv->year, (int) $data['mes'], 1);
                            $novo = $base->copy()->day(min($dv->day, $base->daysInMonth))->toDateString();
                        }
                        if ($novo === $dv->toDateString()) { $ign++; continue; }
                        if ($r->inter_codigo_solicitacao) {
                            try { app(\App\Services\InterBoletoService::class)->reemitir($r, ['data_vencimento' => $novo]); }
                            catch (\Throwable $e) { $falha++; continue; }
                        } else {
                            $r->update(['data_vencimento' => $novo]);
                        }
                        $ok++;
                    }
                    Notification::make()->title($ok . ' atualizado(s), ' . $ign . ' ignorado(s)' . ($falha ? ', ' . $falha . ' falha(s) ao reemitir (conferir no Inter)' : ''))->success()->send();
                }),
            \Filament\Actions\BulkAction::make('reemitirBoletosLote')->label('Cancelar e Reemitir Boletos')->icon('heroicon-o-arrow-path')->color('warning')
                ->schema([
                    DatePicker::make('data_vencimento')->label('Novo vencimento (opcional)')->displayFormat('d/m/Y')->minDate(now())
                        ->helperText('Em branco mantém o vencimento de cada título. Títulos já vencidos exigem nova data, pois o Inter não emite boleto com vencimento passado.'),
                ])
                ->modalDescription('Cancela o boleto atual no Inter e emite um novo para cada título selecionado com boleto. Recebidos, cancelados e sem boleto são ignorados. Para lotes grandes, faça em partes de até 30.')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records, array $data) {
                    set_time_limit(300);
                    $svc = app(\App\Services\InterBoletoService::class);
                    $novaData = $data['data_vencimento'] ?? null;
                    $ok = 0; $ign = 0; $vencidos = 0; $erros = [];
                    foreach ($records as $r) {
                        if (in_array($r->status, ['recebido', 'cancelado']) || ! $r->inter_codigo_solicitacao) { $ign++; continue; }
                        if (! $novaData && \Carbon\Carbon::parse($r->data_vencimento)->lt(today())) { $vencidos++; continue; }
                        try {
                            $svc->reemitir($r, $novaData ? ['data_vencimento' => $novaData] : []);
                            $ok++;
                            usleep(300000);
                        } catch (\Throwable $e) {
                            $erros[] = '#' . $r->id . ': ' . mb_substr($e->getMessage(), 0, 120);
                        }
                    }
                    $titulo = $ok . ' reemitido(s), ' . $ign . ' ignorado(s)' . ($vencidos ? ', ' . $vencidos . ' vencido(s) sem nova data' : '') . ($erros ? ', ' . count($erros) . ' erro(s)' : '');
                    $n = Notification::make()->title($titulo);
                    if ($erros) { $n->body(implode("\n", array_slice($erros, 0, 5)) . "\nTítulos com erro podem estar sem boleto: use Emitir Boleto Inter.")->warning(); } else { $n->success(); }
                    $n->send();
                }),
            DeleteBulkAction::make(),
        ])])
        ->defaultSort('data_vencimento')->dragReorderableColumns()->stickableColumns();
    }
    protected static function getListTableColumns(): array
    {
        return [
            TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
            TextColumn::make('numero_documento')->sortable()->label('Nº Doc.')->searchable()->placeholder('—'),
            TextColumn::make('projeto.nome')->sortable()->label('Projeto')->searchable()->placeholder('—')->badge()->color(fn ($record) => $record->projeto->cor ?? $record->unidade->projeto->cor ?? 'gray'),
            TextColumn::make('planoConta.nome')->sortable()->label('Plano de Conta')->searchable()->placeholder('—'),
            TextColumn::make('cliente.nome')->sortable()->label('Cliente')->searchable()->placeholder('—'),
            TextColumn::make('descricao')->sortable()->label('Descrição')->searchable()->weight('medium')->limit(40),
            TextColumn::make('valor')->label('Valor')->money('BRL')->alignEnd()->sortable(),
            TextColumn::make('contaBancaria.nome')->sortable()->label('Conta Bancária')->searchable()->placeholder('—'),
            TextColumn::make('status')->sortable()->label('Status')->badge()
                ->colors(['gray' => 'aberto', 'success' => 'recebido', 'danger' => 'vencido', 'warning' => 'cancelado'])
                ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state }),
        ];
    }
    protected static function getGridTableColumns(): array
    {
        return [
            Stack::make([
                Split::make([
                    TextColumn::make('descricao')->sortable()->label('Descrição')->searchable()->weight('medium')->limit(40),
                    TextColumn::make('status')->sortable()->label('Status')->badge()
                        ->colors(['gray' => 'aberto', 'success' => 'recebido', 'danger' => 'vencido', 'warning' => 'cancelado'])
                        ->formatStateUsing(fn ($state) => match($state) { 'aberto' => 'Aberto', 'recebido' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado', default => $state })
                        ->grow(false),
                ]),
                TextColumn::make('cliente.nome')->sortable()->label('Cliente')->description('Cliente', position: 'above')->searchable()->placeholder('—'),
                Split::make([
                    TextColumn::make('valor')->sortable()->label('Valor')->description('Valor', position: 'above')->money('BRL')->alignEnd(),
                    TextColumn::make('valor_recebido')->sortable()->label('Recebido')->description('Recebido', position: 'above')->money('BRL')->alignEnd(),
                    TextColumn::make('data_vencimento')->sortable()->label('Vencimento')->description('Vencimento', position: 'above')->date('d/m/Y'),
                ]),
            ])->space(3)->extraAttributes(['class' => 'pb-2']),
        ];
    }
    public static function getPages(): array
    {
        return ['index' => ListContasReceber::route('/')];
    }
}
