<?php

namespace App\Filament\Resources;

use App\Enums\VinculoParceiroEnum;
use App\Filament\Resources\EquipamentoResource\Pages;
use App\Filament\Resources\EquipamentoResource\RelationManagers;
use App\Models\Equipamento;
use App\Models\Parceiro;
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
            ->columns(4)
            ->schema([
                static::getParceiroFormField(),
                static::getDescricaoFormField(),
                static::getNroSerieFormField(),
                static::getModeloFormField(),
                static::getMarcaFormField(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query){
                return $query->with('parceiro', 'creator', 'updater');
            })
            ->columns([
                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nro_serie')
                    ->label('Nro. Série')
                    ->searchable(),

                Tables\Columns\TextColumn::make('modelo'),

                Tables\Columns\TextColumn::make('marca'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Criado por')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updater.name')
                    ->label('Atualizado por')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('tipo_vinculo')
                    ->options(VinculoParceiroEnum::class)
                    ->label('Vínculo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\RestoreAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->slideOver()
                    ->label('Filtros'))
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
            'index' => Pages\ListEquipamentos::route('/'),
            'create' => Pages\CreateEquipamento::route('/create'),
            'edit' => Pages\EditEquipamento::route('/{record}/edit'),
        ];
    }
    
    public static function getParceiroFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parceiro_id')
                    ->columnSpan(1)
                    ->label('Parceiro')
                    ->relationship('parceiro', 'nome')
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Parceiro::where('tipo_vinculo', 'cliente')
                                        ->where('ativo', true)
                                        ->pluck('nome', 'id');
                    })
                    ->required();
    }
    
    public static function getDescricaoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('descricao')
                    ->columnSpan(2)
                    ->label('Descrição')
                    ->autocomplete(false)
                    ->maxLength(255);
    }
    
    public static function getNroSerieFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('nro_serie')
                    ->columnSpan(1)
                    ->label('Nro. Série')
                    ->autocomplete(false)
                    ->maxLength(255);
    }
    
    public static function getModeloFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('modelo')
                    ->columnSpan(1)
                    ->autocomplete(false)
                    ->maxLength(255);
    }
    
    public static function getMarcaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('marca')
                    ->columnSpan(1)
                    ->autocomplete(false)
                    ->maxLength(255);
    }
}
