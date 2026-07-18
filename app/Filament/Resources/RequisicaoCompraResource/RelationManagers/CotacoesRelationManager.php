<?php
namespace App\Filament\Resources\RequisicaoCompraResource\RelationManagers;
use App\Models\RequisicaoCompra;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Forms\Form;
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

    public function form(Form $form): Form
    {
        /** @var RequisicaoCompra $requisicao */
        $requisicao = $this->getOwnerRecord();

        return $form->schema([
            Forms\Components\Select::make('fornecedor_id')->label('Fornecedor')
                ->options(Fornecedor::where('ativo', true)->pluck('nome', 'id'))->searchable()->native(false)->required(),
            Forms\Components\DatePicker::make('data_cotacao')->label('Data da Cotação')->native(false)->displayFormat('d/m/Y')->default(now())->required(),
            Forms\Components\TextInput::make('prazo_entrega_dias')->label('Prazo de Entrega (dias)')->numeric()->minValue(0),
            Forms\Components\TextInput::make('condicao_pagamento')->label('Condição de Pagamento')->maxLength(100)->placeholder('Ex: 30/60/90 dias'),
            Forms\Components\FileUpload::make('arquivo_path')->label('Anexo da Cotação')->directory('cotacoes-compra')->nullable(),
            Forms\Components\Repeater::make('itens')->relationship()->label('Preços por Item')
                ->schema([
                    Forms\Components\Select::make('item_requisicao_compra_id')->label('Item')
                        ->options($requisicao->itens->pluck('descricao', 'id'))->required()->native(false),
                    Forms\Components\TextInput::make('valor_unitario')->label('Valor Unitário')->numeric()->prefix('R$')->step(0.01)->default(0)->required(),
                ])
                ->columns(2)->defaultItems(0)->addActionLabel('+ Adicionar Preço')->columnSpanFull(),
            Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fornecedor.nome')
            ->columns([
                Tables\Columns\TextColumn::make('fornecedor.nome')->label('Fornecedor')->weight('medium'),
                Tables\Columns\TextColumn::make('data_cotacao')->label('Data')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('prazo_entrega_dias')->label('Prazo')->suffix(' dia(s)')->placeholder('—'),
                Tables\Columns\TextColumn::make('condicao_pagamento')->label('Pagamento')->placeholder('—'),
                Tables\Columns\TextColumn::make('valor_total')->label('Valor Total')->money('BRL')->sortable(),
                Tables\Columns\IconColumn::make('vencedora')->label('Vencedora')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('+ Registrar Cotação')
                    ->slideOver()->modalWidth('4xl')
                    ->visible(fn () => $this->getOwnerRecord()->podeReceberCotacao())
                    ->modalDescription(fn () => sprintf('%d de %d cotações registradas.', $this->getOwnerRecord()->cotacoes()->count(), RequisicaoCompra::MAX_COTACOES)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver()->modalWidth('4xl')->visible(fn ($record) => ! $record->vencedora),
                Tables\Actions\DeleteAction::make()->visible(fn ($record) => ! $record->vencedora),
            ])
            ->emptyStateHeading('Nenhuma cotação registrada')
            ->emptyStateDescription('Registre até '.RequisicaoCompra::MAX_COTACOES.' cotações de fornecedores diferentes para comparar antes de gerar o pedido.');
    }
}
