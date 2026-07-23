<?php
namespace App\Filament\Resources\RequisicaoCompraResource\RelationManagers;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Models\RequisicaoCompra;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CotacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'cotacoes';
    protected static ?string $title = 'Cotações';
    protected static ?string $modelLabel = 'Cotação';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return in_array($ownerRecord->status, ['em_cotacao', 'cotada', 'pedido_gerado']);
    }

    public function form(Schema $schema): Schema
    {
        /** @var RequisicaoCompra $requisicao */
        $requisicao = $this->getOwnerRecord();

        return $schema->components([
            Select::make('fornecedor_id')->label('Fornecedor')
                ->options(Fornecedor::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
            DatePicker::make('data_cotacao')->label('Data da Cotação')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
            TextInput::make('prazo_entrega_dias')->label('Prazo de Entrega (dias)')->numeric()->minValue(0),
            TextInput::make('condicao_pagamento')->label('Condição de Pagamento')->maxLength(100)->placeholder('Ex: 30/60/90 dias'),
            FileUpload::make('arquivo_path')->label('Anexo da Cotação')->directory('cotacoes-compra')->nullable(),
            Repeater::make('itens')->relationship()->label('Preços por Item')
                ->schema([
                    Select::make('item_requisicao_compra_id')->label('Item')
                        ->options($requisicao->itens->pluck('descricao', 'id'))->required()->native(false),
                    TextInput::make('valor_unitario')->label('Valor Unitário')->numeric()->prefix('R$')->step(0.01)->default(0)->required(),
                ])
                ->columns(2)->defaultItems(0)->addActionLabel('+ Adicionar Preço')->columnSpanFull(),
            Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fornecedor.nome')
            ->columns([
                TextColumn::make('fornecedor.nome')->label('Fornecedor')->weight('medium'),
                TextColumn::make('data_cotacao')->label('Data')->date('d/m/Y'),
                TextColumn::make('prazo_entrega_dias')->label('Prazo')->suffix(' dia(s)')->placeholder('—'),
                TextColumn::make('condicao_pagamento')->label('Pagamento')->placeholder('—'),
                TextColumn::make('valor_total')->label('Valor Total')->money('BRL')->sortable(),
                IconColumn::make('vencedora')->label('Vencedora')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Registrar Cotação')
                    ->slideOver()->modalWidth('4xl')
                    ->visible(fn () => $this->getOwnerRecord()->podeReceberCotacao())
                    ->modalDescription(fn () => sprintf('%d de %d cotações registradas.', $this->getOwnerRecord()->cotacoes()->count(), RequisicaoCompra::MAX_COTACOES)),
            ])
            ->recordActions([
                EditAction::make()->slideOver()->modalWidth('4xl')->visible(fn ($record) => ! $record->vencedora),
                DeleteAction::make()->visible(fn ($record) => ! $record->vencedora),
            ])
            ->emptyStateHeading('Nenhuma cotação registrada')
            ->emptyStateDescription('Registre até '.RequisicaoCompra::MAX_COTACOES.' cotações de fornecedores diferentes para comparar antes de gerar o pedido.');
    }
}
