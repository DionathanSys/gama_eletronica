<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContaReceberResource\Pages;
use App\Filament\Resources\ContaReceberResource\RelationManagers;
use App\Models\ContaReceber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContaReceberResource extends Resource
{
    protected static ?string $model = ContaReceber::class;

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?string $pluralModelLabel = 'Contas à Receber';
    
    protected static ?string $navigationLabel = 'Contas à Receber';

    protected static ?string $title = 'Contas à Receber';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getParceiroFormField(),
                static::getFaturaFormField(),
                static::getDataVencimentoFormField(),
                static::getValorFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fatura.id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_vencimento')
                    ->label('Vencimento')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('desdobramento')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('desdobramentos')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListContaRecebers::route('/'),
            'create' => Pages\CreateContaReceber::route('/create'),
            'edit' => Pages\EditContaReceber::route('/{record}/edit'),
        ];
    }

    public static function getParceiroFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parceiro_id')
                ->relationship('parceiro', 'nome')
                ->columnSpan(2);
    } 

    public static function getFaturaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('fatura_id')
                ->label('Fatura')
                ->columnSpan(1);
    } 

    public static function getDataVencimentoFormField(): Forms\Components\DatePicker
    {
        return Forms\Components\DatePicker::make('data_vencimento')
                ->label('Vencimento')
                ->native(false)
                ->columnSpan(1);
    } 

    public static function getValorFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('valor')
                ->numeric()
                ->prefix('R$')
                ->columnSpan(1);
    } 

    public static function getStatusFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('status')
                ->columnSpan(1)
                ->disabled();
    } 
}
