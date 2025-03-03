<?php

namespace App\Filament\Resources\NotaSaidaResource\RelationManagers;

use App\Enums\StatusNotaFiscalEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItensRelationManager extends RelationManager
{
    protected static string $relationship = 'itens';

    protected static ?string $title = 'Itens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantidade')
                    ->minValue(1)
                    ->numeric()
                    ->required()
                    ->disabled(fn():bool => $this->ownerRecord->status != StatusNotaFiscalEnum::PENDENTE),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => $this->ownerRecord->status == StatusNotaFiscalEnum::PENDENTE)
                    ->iconButton(),
            ])
            ->bulkActions([
            ]);
    }
}
