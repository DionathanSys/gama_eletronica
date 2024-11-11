<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

use App\Actions\OrdemServico\CreateItemOrdemActions;
use App\Actions\OrdemServico\UpdateValorOrdemActions;
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
                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço'),

                Tables\Columns\TextColumn::make('quantidade'),

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
                    ->label('Adicionar Serviço')
                    ->beforeFormFilled(function(Tables\Actions\CreateAction $action){
                        
                        //Verifica o status da ordem de serviço
                        
                        if($this->getOwnerRecord()->status != 'pendente') {
                            Notification::make()
                                ->warning()
                                ->title('Inclusão Bloqueada')
                                ->body('Não é permitido a inclusão de itens, com o status atual da ordem de serviço.')
                                ->send();
                                $action->cancel();
                                return;
                        }
                    
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                 
                        return $data;
                    })
                    ->after(function(){
                        return UpdateValorOrdemActions::exec($this->getOwnerRecord());
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function(ItemOrdemServico $record){
                        return UpdateValorOrdemActions::exec($this->getOwnerRecord());
                }),
                
                Tables\Actions\DeleteAction::make()
                    ->after(function(ItemOrdemServico $record){
                        return UpdateValorOrdemActions::exec($this->getOwnerRecord());
                }),
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
                    ->columnSpan(4)
                    ->relationship('servico', 'nome')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $valorUnitarioServico = (Servico::find($get('servico_id')))->valor_unitario;
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
                        $set('valor_total', $quantidade * $valor_unitario);
                    });
    }
   
    public static function getValorUnitarioFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('valor_unitario')
                    ->columnSpan(1)
                    ->prefix('R$')
                    ->minValue(0.01)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $quantidade = $get('quantidade') ?? 1;
                        $valor_unitario = $get('valor_unitario') ?? 0.01;
                        $set('valor_total', $quantidade * $valor_unitario);
                    });
    }

    public static function getValorTotalFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('valor_total')
                    ->columnSpan(1)
                    ->prefix('R$')
                    ->minValue(0.01)
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(Forms\Set $set, Forms\Get $get){
                        $quantidade = $get('quantidade') ?? 1;
                        $valor_total = $get('valor_total') ?? 0.01;
                        $valor_unitario = number_format($valor_total / $quantidade, 2);

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
