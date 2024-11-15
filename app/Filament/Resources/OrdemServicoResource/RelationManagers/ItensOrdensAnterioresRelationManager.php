<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

use App\Filament\Resources\OrdemServicoResource;
use App\Models\ItemOrdemServico;
use App\Models\OrdemServico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItensOrdensAnterioresRelationManager extends RelationManager
{
    protected static string $relationship = 'itensOrdensAnteriores';

    protected static ?string $title = 'Histórico Equipamento';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('ordemServico.id')
                    ->label('Nro. OS'),
                
                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço'),

                Tables\Columns\TextColumn::make('quantidade'),

                Tables\Columns\TextColumn::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('valor_total')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação'),
                
                Tables\Columns\TextColumn::make('ordemServico.data_ordem')
                    ->label('Data')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('visualizar')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->url(fn(ItemOrdemServico $record) => OrdemServicoResource::getUrl('edit', ['record' => $record->ordem_servico_id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
               
            ])
            ->paginated([10, 25, 50, 100])
            ->striped()
            ;
    }
}
