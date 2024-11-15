<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

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

class ItensOrcamentoRelationManager extends RelationManager
{
    protected static string $relationship = 'itens_orcamento';

    protected static ?string $title = 'Orçamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('+ Item Orçamento')
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
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->after(function(ItemOrdemServico $record){
                //         return UpdateValorOrdemActions::exec($this->getOwnerRecord());
                // }),
                // Tables\Actions\DeleteAction::make()
                //     ->after(function(ItemOrdemServico $record){
                //         return UpdateValorOrdemActions::exec($this->getOwnerRecord());
                // }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
