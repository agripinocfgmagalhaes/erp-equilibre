<?php

namespace App\Filament\Exports;

use App\Models\ContaReceber;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ContaReceberExporter extends Exporter
{
    protected static ?string $model = ContaReceber::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('descricao')->label('Descrição'),
            ExportColumn::make('cliente.nome')->label('Cliente'),
            ExportColumn::make('numero_documento')->label('Nº Documento'),
            ExportColumn::make('projeto.nome')->label('Empreendimento'),
            ExportColumn::make('planoConta.nome')->label('Plano de Conta'),
            ExportColumn::make('valor')->label('Valor'),
            ExportColumn::make('valor_recebido')->label('Valor Recebido'),
            ExportColumn::make('data_vencimento')->label('Vencimento')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y')),
            ExportColumn::make('data_recebimento')->label('Data Recebimento')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y')),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('contaBancaria.nome')->label('Conta Bancária'),
            ExportColumn::make('inter_codigo_solicitacao')->label('Código Boleto Inter'),
            ExportColumn::make('inter_situacao')->label('Situação Boleto Inter'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Sua exportação de Contas a Receber terminou e ' . number_format($export->successful_rows) . ' ' . str('linha')->plural($export->successful_rows) . ' foram exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('linha')->plural($failedRowsCount) . ' falharam ao exportar.';
        }

        return $body;
    }
}
