<?php

namespace App\Filament\Resources\NotaSaidaResource\RelationManagers;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\StatusNotaFiscalEnum;
use App\Filament\Resources\EquipamentoResource;
use App\Filament\Resources\OrdemServicoResource;
use App\Models\Equipamento;
use App\Models\ItemNotaSaida;
use App\Traits\DefineCfop;
use App\Traits\DefineImposto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItensRelationManager extends RelationManager
{
    use DefineCfop, DefineImposto;

    protected static string $relationship = 'itens';

    protected static ?string $title = 'Itens';

    public function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\Select::make('produto')
                    ->label('Produto')
                    ->visibleOn('create')
                    ->searchable()
                    ->columnSpanFull()
                    ->preload()
                    ->options(
                        ItemNotaSaida::query()
                            ->get()
                            ->pluck('descricao_produto', 'id')
                    )
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $item = ItemNotaSaida::find($state);
                            $set('codigo_produto', $item->codigo_produto);
                            $set('descricao_produto', $item->descricao_produto);
                            $set('ncm', $item->ncm);
                        }
                    }),
                Forms\Components\TextInput::make('ncm')
                    ->label('Cód. NCM')
                    ->columnSpan(2)
                    ->autocomplete(false)
                    ->visibleOn('create')
                    ->length(8)
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('codigo_produto')
                    ->label('Código')
                    ->columnSpan(2)
                    ->autocomplete(false)
                    ->visibleOn('create')
                    ->minLength(3)
                    ->maxLength(15)
                    ->required(),
                Forms\Components\TextInput::make('descricao_produto')
                    ->label('Descrição')
                    ->columnSpan(8)
                    ->autocomplete(false)
                    ->visibleOn('create')
                    ->minLength(3)
                    ->maxLength(100)
                    ->required(),

                Forms\Components\TextInput::make('quantidade')
                    ->autocomplete(false)
                    ->columnSpan(2)
                    ->minValue(1)
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if ($get('valor_unitario')) {
                            $set('valor_total', $state * $get('valor_unitario'));
                        }
                    }),
                Forms\Components\TextInput::make('valor_unitario')
                    ->autocomplete(false)
                    ->columnSpan(4)
                    ->visibleOn('create')
                    ->prefix('R$')
                    ->minValue(1)
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if ($get('quantidade')) {
                            $set('valor_total', $state * $get('quantidade'));
                        }
                    }),
                Forms\Components\TextInput::make('valor_total')
                    ->autocomplete(false)
                    ->columnSpan(4)
                    ->visibleOn('create')
                    ->prefix('R$')
                    ->minValue(1)
                    ->numeric()
                    ->readOnly()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao_produto')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('codigo_produto')
                    ->label('Código'),
                Tables\Columns\TextColumn::make('descricao_produto')
                    ->label('Descrição'),
                Tables\Columns\TextColumn::make('quantidade'),
                Tables\Columns\TextColumn::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('ncm')
                    ->label('NCM'),
                Tables\Columns\TextColumn::make('cfop')
                    ->label('CFOP'),
                Tables\Columns\TextColumn::make('unidade'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Item')
                    ->visible(fn() => in_array(
                        $this->ownerRecord->natureza_operacao,
                        [
                            NaturezaOperacaoEnum::REMESSA_CONSIGNACAO,
                            NaturezaOperacaoEnum::RETORNO_MERCADORIA_DEMO
                        ]
                    ))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['cfop'] = self::getCfop($this->ownerRecord->parceiro, 'nfe_remessa');
                        $data['unidade'] = 'UN';
                        $data['impostos'] = self::getImpostosDefault();
                        unset($data['cadastrar_novo'], $data['produto']);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => $this->ownerRecord->status == StatusNotaFiscalEnum::PENDENTE)
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => $this->ownerRecord->status == StatusNotaFiscalEnum::PENDENTE && $this->ownerRecord->natureza_operacao == NaturezaOperacaoEnum::REMESSA_CONSIGNACAO)
                    ->iconButton(),
            ])
            ->bulkActions([]);
    }
}
