<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContatoResource\Pages;
use App\Filament\Resources\ContatoResource\RelationManagers;
use App\Models\Contato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContatoResource extends Resource
{
    protected static ?string $model = Contato::class;

    protected static ?string $navigationGroup = 'Cadastros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('parceiro_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefone_fixo')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefone_cel')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Toggle::make('envio_ordem')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parceiro_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone_fixo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone_cel')
                    ->searchable(),
                Tables\Columns\IconColumn::make('envio_ordem')
                    ->boolean(),
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
            'index' => Pages\ListContatos::route('/'),
            'create' => Pages\CreateContato::route('/create'),
            'edit' => Pages\EditContato::route('/{record}/edit'),
        ];
    }
}
