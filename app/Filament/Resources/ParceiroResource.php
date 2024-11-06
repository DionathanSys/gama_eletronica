<?php

namespace App\Filament\Resources;

use App\Enums\VinculoParceiroEnum;
use App\Filament\Resources\ContatoResource\RelationManagers\ContatoRelationManager;
use App\Filament\Resources\ParceiroResource\Pages;
use App\Filament\Resources\ParceiroResource\RelationManagers;
use App\Filament\Resources\ParceiroResource\RelationManagers\EnderecosRelationManager;
use App\Models\Parceiro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParceiroResource extends Resource
{
    protected static ?string $model = Parceiro::class;

    protected static ?string $navigationGroup = 'Cadastros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipo_vinculo')
                    ->required()
                    ->options(VinculoParceiroEnum::class),

                Forms\Components\Select::make('tipo_documento')
                    ->required()
                    ->options(['CPF' => 'CPF', 'CNPJ' => 'CNPJ'])
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('nro_documento', null))
                    ->default('CNPJ'),

                Forms\Components\TextInput::make('nro_documento')
                    ->required()
                    ->length(fn(Get $get) => $get('tipo_documento') == 'CPF' ? 14 : 18)
                    ->mask(function(Get $get) {
                        if ($get('tipo_documento') == 'CPF'){
                            return '999.999.999-99';
                        }

                        return '99.999.999/9999-99';
                    }),

                Forms\Components\Toggle::make('ativo')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo_vinculo'),

                Tables\Columns\TextColumn::make('tipo_documento'),

                Tables\Columns\TextColumn::make('nro_documento')
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('ativo')
                    ->toggleable()
                    ->beforeStateUpdated(function ($record, $state) {
                        $record->update(['status' => !$state]);
                    }),

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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EnderecosRelationManager::class,
            ContatoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParceiros::route('/'),
            'create' => Pages\CreateParceiro::route('/create'),
            'edit' => Pages\EditParceiro::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
