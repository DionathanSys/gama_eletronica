<?php

namespace App\Filament\Resources\ParceiroResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EnderecosRelationManager extends RelationManager
{
    protected static string $relationship = 'enderecos';

    protected static ?string $title = 'Endereços';

    public function form(Form $form): Form
    {
        return $form
            ->columns(5)
            ->schema([
                Forms\Components\TextInput::make('rua')
                    ->columnSpan(2)
                    ->required()
                    ->maxLength(75),

                Forms\Components\TextInput::make('numero')
                    ->label('Número')
                    ->columnSpan(1)
                    ->required()
                    ->maxLength(7),

                Forms\Components\TextInput::make('complemento')
                    ->columnSpan(1)
                    ->maxLength(10),

                Forms\Components\TextInput::make('bairro')
                    ->columnSpan(1)
                    ->required()
                    ->maxLength(75),

                Forms\Components\TextInput::make('codigo_municipio')
                    ->label('Cód. Município')
                    ->columnSpan(1)
                    ->maxLength(10),

                Forms\Components\TextInput::make('cidade')
                    ->columnSpan(1)
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('estado')
                    ->columnSpan(1)
                    ->required()
                    ->maxLength(2)
                    ->default('SC'),

                Forms\Components\TextInput::make('cep')
                    ->columnSpan(1)
                    ->label('CEP')
                    ->required()
                    ->maxLength(9),

                Forms\Components\TextInput::make('pais')
                    ->required()
                    ->maxLength(15)
                    ->default('Brasil'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('parceiro_id')
            ->recordTitle('Endereço')
            ->columns([
                Tables\Columns\TextColumn::make('rua'),

                Tables\Columns\TextColumn::make('numero'),

                Tables\Columns\TextColumn::make('complemento'),

                Tables\Columns\TextColumn::make('bairro'),

                Tables\Columns\TextColumn::make('codigo_municipio'),

                Tables\Columns\TextColumn::make('cidade'),

                Tables\Columns\TextColumn::make('estado'),

                Tables\Columns\TextColumn::make('cep')
                    ->label('CEP'),

                Tables\Columns\TextColumn::make('pais')
                    ->label('País')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y h:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime('d/m/Y h:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Endereço')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                 
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
