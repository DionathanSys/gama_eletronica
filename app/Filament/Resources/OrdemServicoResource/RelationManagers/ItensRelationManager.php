<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

use App\Actions\OrdemServico\CreateItemOrdemActions;
use App\Actions\OrdemServico\UpdateValorOrdemActions;
use App\Enums\StatusOrdemServicoEnum;
use App\Models\ItemOrdemServico;
use App\Models\Servico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItensRelationManager extends RelationManager
{
    protected static string $relationship = 'itens';

    protected static ?string $title = 'Serviços';

    public function form(Form $form): Form
    {
        return $form
            ->columns(4)
            ->schema([
                static::getServicoFormField(),
                static::getQuantidadeFormField(),
                static::getValorUnitarioFormField(),
                static::getValorTotalFormField(),
                static::getObservacaoFormField(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->formatStateUsing(fn($state)=> str_pad($state, 5, '0', STR_PAD_LEFT)),

                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->numeric(2 , ',', '.'),

                Tables\Columns\TextColumn::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Total')),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Serviço')
                    ->icon('heroicon-o-plus')
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                 
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false),
                
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getServicoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('servico_id')
                    ->label('Serviço')
                    ->columnSpan(4)
                    ->relationship('servico', 'nome')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $valorUnitarioServico = (Servico::find($get('servico_id')))->valor_unitario ?? 1;
                        $set('valor_unitario', $valorUnitarioServico ?? 0.01);
                        $set('valor_total', $valorUnitarioServico * $get('quantidade'));
                    });
    }

    public static function getQuantidadeFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('quantidade')
                    ->columnSpan(1)
                    ->minValue(0.01)
                    ->numeric()
                    ->default(1)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $quantidade = $get('quantidade') ?? 1;
                        $valor_unitario = $get('valor_unitario') ?? 0.01;
                        $set('valor_total', number_format(($quantidade * $valor_unitario), 2));
                    });
    }
   
    public static function getValorUnitarioFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->columnSpan(1)
                    ->prefix('R$')
                    ->minValue(0.01)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $quantidade = $get('quantidade') ?? 1;
                        $valor_unitario = $get('valor_unitario') ?? 0.01;
                        $set('valor_total', number_format(($quantidade * $valor_unitario), 2));
                    });
    }

    public static function getValorTotalFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('valor_total')
                    ->label('Valor Total')
                    ->columnSpan(1)
                    ->prefix('R$')
                    ->minValue(0.01)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $quantidade = $get('quantidade') ?? 1;
                        $valor_total = number_format(($get('valor_total') ?? 0.01), 2);
                        $valor_unitario = number_format(($valor_total / $quantidade), 2);

                        if($valor_unitario > 0.01){
                            $set('valor_unitario', $valor_unitario);
                        } else {
                            $set('valor_unitario', 0.01);
                            $set('valor_total', $quantidade * 0.01);
                        }

                    });
    }
   
    public static function getDescontoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('desconto')
                    ->columnSpan(1)
                    ->suffix('%')
                    ->minValue(0)
                    ->numeric()
                    ->default(0);
    }

    public static function getObservacaoFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('observacao')
                    ->label('Observação')
                    ->columnSpan(4)
                    ->maxLength(255);
    }
}
