<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicoResource\Pages;
use App\Filament\Resources\ServicoResource\RelationManagers;
use App\Models\Servico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicoResource extends Resource
{
    protected static ?string $model = Servico::class;

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $pluralModelLabel = 'Serviços';

    protected static ?string $navigationLabel = 'Serviços';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(5)
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->columnSpan(2)
                    ->required()
                    ->autocomplete(false)
                    ->maxLength(170),

                Forms\Components\TextInput::make('descricao')
                    ->columnSpan(2)
                    ->label('Descrição')
                    ->autocomplete(false)
                    ->maxLength(250),
                
                Forms\Components\TextInput::make('valor_unitario')
                    ->columnSpan(1)
                    ->label('Vlr. Unitário')
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('R$'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('ativo'),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServicos::route('/'),
            // 'create' => Pages\CreateServico::route('/create'),
            // 'edit' => Pages\EditServico::route('/{record}/edit'),
        ];
    }
}
