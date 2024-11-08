<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpostoServicoResource\Pages;
use App\Filament\Resources\ImpostoServicoResource\RelationManagers;
use App\Models\ImpostoServico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImpostoServicoResource extends Resource
{
    protected static ?string $model = ImpostoServico::class;

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $pluralModelLabel = 'Imposto Serviços';

    protected static ?string $navigationLabel = 'Imposto Serviços';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_municipio')
                    ->label('Código Município')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('municipio')
                    ->label('Município')
                    ->required()
                    ->maxLength(150),

                Forms\Components\TextInput::make('codigo_servico')
                    ->label('Código Serviço')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('aliq_iss')
                    ->label('Alíquota ISS')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_municipio')
                    ->label('Código Município')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('municipio')
                    ->label('Município')
                    ->searchable(),

                Tables\Columns\TextColumn::make('codigo_servico')
                    ->label('Código Serviço')
                    ->searchable(),

                Tables\Columns\TextColumn::make('aliq_iss')
                    ->label('Alíquota ISS')
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageImpostoServicos::route('/'),
        ];
    }
}
