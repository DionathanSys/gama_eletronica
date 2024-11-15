<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnderecoResource\Pages;
use App\Filament\Resources\EnderecoResource\RelationManagers;
use App\Models\Endereco;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnderecoResource extends Resource
{
    protected static ?string $model = Endereco::class;

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $pluralModelLabel = 'Endereços';

    protected static ?string $navigationLabel = 'Endereços';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parceiro_id')
                    ->required()
                    ->searchable()
                    ->relationship('parceiro', 'nome'),

                Forms\Components\TextInput::make('rua')
                    ->required()
                    ->maxLength(75),

                Forms\Components\TextInput::make('numero')
                    ->required()
                    ->maxLength(7),

                Forms\Components\TextInput::make('complemento')
                    ->maxLength(10),

                Forms\Components\TextInput::make('bairro')
                    ->required()
                    ->maxLength(75),

                Forms\Components\TextInput::make('codigo_municipio')
                    ->maxLength(10),

                Forms\Components\TextInput::make('cidade')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->maxLength(2)
                    ->default('SC'),

                Forms\Components\TextInput::make('cep')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('pais')
                    ->required()
                    ->maxLength(255)
                    ->default('Brasil'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query){
                return $query->with('parceiro');
            })
            ->columns([
                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rua')
                    ->searchable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Nro.')
                    ->searchable(),

                Tables\Columns\TextColumn::make('complemento')
                    ->searchable(),

                Tables\Columns\TextColumn::make('bairro')
                    ->searchable(),

                Tables\Columns\TextColumn::make('codigo_municipio')
                    ->label('Código Município')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cidade')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cep')
                    ->label('CEP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pais')
                    ->label('País')
                    ->searchable(),

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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->striped();
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
            'index' => Pages\ListEnderecos::route('/'),
            // 'create' => Pages\CreateEndereco::route('/create'),
            // 'edit' => Pages\EditEndereco::route('/{record}/edit'),
        ];
    }
}
