<?php

namespace App\Filament\Resources\FaturaResource\RelationManagers;

use App\Filament\Resources\OrdemServicoResource;
use App\Models\OrdemServico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdensServicoRelationManager extends RelationManager
{
    protected static string $relationship = 'ordensServico';

    protected static ?string $title = 'Ordens de Serviço';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nro. OS')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('equipamento.descricao')
                    ->label('Descrição')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('equipamento.nro_serie')
                    ->label('Nro. Série')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fatura_id')
                    ->label('Fatura')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('data_ordem')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable()
                    ->summarize(Sum::make()->money('BRL')->label('Total')),

                Tables\Columns\TextColumn::make('desconto')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\Action::make('visualizar')
                    ->url(fn(OrdemServico $record) => OrdemServicoResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
            ]);
    }
}
