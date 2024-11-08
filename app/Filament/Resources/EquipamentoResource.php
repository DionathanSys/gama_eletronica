<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipamentoResource\Pages;
use App\Filament\Resources\EquipamentoResource\RelationManagers;
use App\Models\Equipamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipamentoResource extends Resource
{
    protected static ?string $model = Equipamento::class;

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $pluralModelLabel = 'Equipamentos';

    protected static ?string $navigationLabel = 'Equipamentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parceiro_id')
                    ->relationship('parceiro', 'id')
                    ->required(),

                Forms\Components\TextInput::make('descricao')
                    ->maxLength(255),

                Forms\Components\TextInput::make('nro_serie')
                    ->maxLength(255),

                Forms\Components\TextInput::make('modelo')
                    ->maxLength(255),

                Forms\Components\TextInput::make('marca')
                    ->maxLength(255),

                Forms\Components\TextInput::make('created_by')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('updated_by')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parceiro.id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nro_serie')
                    ->searchable(),

                Tables\Columns\TextColumn::make('modelo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('marca')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Exluído Em')
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
            'index' => Pages\ListEquipamentos::route('/'),
            'create' => Pages\CreateEquipamento::route('/create'),
            'edit' => Pages\EditEquipamento::route('/{record}/edit'),
        ];
    }
}
