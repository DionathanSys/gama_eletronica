<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaturaResource\Pages;
use App\Filament\Resources\FaturaResource\RelationManagers;
use App\Models\Fatura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
            ->schema([
                Forms\Components\TextInput::make('parceiro_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('valor_total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('desconto')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path_pdf')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parceiro_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desconto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('path_pdf')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
