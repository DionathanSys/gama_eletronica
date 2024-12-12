<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaturaResource\Pages;
use App\Filament\Resources\FaturaResource\RelationManagers;
use App\Filament\Resources\FaturaResource\RelationManagers\ContasReceberRelationManager;
use App\Filament\Resources\FaturaResource\RelationManagers\OrdensServicoRelationManager;
use App\Models\Fatura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FaturaResource extends Resource
{
    protected static ?string $model = Fatura::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\Select::make('parceiro_id')
                    ->disabled()
                    ->columnSpan(4)
                    ->label('Parceiro')
                    ->relationship('parceiro', 'nome'),

                Forms\Components\TextInput::make('valor_total')
                ->numeric()
                    ->disabled()
                    ->columnSpan(2)
                    ->label('Valor Total')
                    ->prefix('R$'),
                
                Forms\Components\TextInput::make('desconto')
                ->numeric()
                    ->disabled()
                    ->columnSpan(2)
                    ->label('Desconto')
                    ->prefix('R$'),
                    
                Forms\Components\TextInput::make('valor_total')
                    ->formatStateUsing(fn($record) => $record->valor_total - $record->desconto)
                    ->numeric()
                    ->disabled()
                    ->columnSpan(2)
                    ->label('Valor Liquído')
                    ->prefix('R$'),

                Forms\Components\TextInput::make('status')
                    ->disabled()
                    ->columnSpan(2),
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nro. Fatura')
                    ->searchable()
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->searchable()
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Total')),
                    
                Tables\Columns\TextColumn::make('desconto')
                    ->sortable()
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Descontos'))
                    ->toggleable(isToggledHiddenByDefault: false),

                // Tables\Columns\TextColumn::make('valor_liquido')
                //     ->sortable()
                //     ->money('BRL')
                //     ->summarize(Sum::make()->money('BRL')->label('Valor Liquído'))
                //     ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultSortOptionLabel('Date');
    }

    public static function getRelations(): array
    {
        return [
            OrdensServicoRelationManager::class,
            ContasReceberRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaturas::route('/'),
            'create' => Pages\CreateFatura::route('/create'),
            'edit' => Pages\EditFatura::route('/{record}/edit'),
        ];
    }
}
