<?php

namespace App\Filament\Resources;

use App\Enums\VinculoParceiroEnum;
use App\Filament\Resources\ParceiroResource\Pages;
use App\Filament\Resources\ParceiroResource\RelationManagers;
use App\Filament\Resources\ParceiroResource\RelationManagers\ContatoRelationManager;
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
            ->columns(5)
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->autocomplete(false)
                    ->columnSpan(2)
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipo_vinculo')
                    ->label('Vínculo')
                    ->columnSpan(1)
                    ->required()
                    ->default(VinculoParceiroEnum::CLIENTE)
                    ->options(VinculoParceiroEnum::class),

                Forms\Components\Select::make('tipo_documento')
                    ->columnSpan(1)
                    ->required()
                    ->options(['CPF' => 'CPF', 'CNPJ' => 'CNPJ'])
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('nro_documento', null))
                    ->default('CNPJ'),

                Forms\Components\TextInput::make('nro_documento')
                    ->autocomplete(false)
                    ->columnSpan(1)
                    ->required()
                    ->length(fn(Get $get) => $get('tipo_documento') == 'CPF' ? 14 : 18)
                    ->mask(function (Get $get) {
                        if ($get('tipo_documento') == 'CPF') {
                            return '999.999.999-99';
                        }

                        return '99.999.999/9999-99';
                    }),

                Forms\Components\TextInput::make('inscricao_estadual')
                    ->label('Inscrição estadual')
                    ->columnSpan(1)
                    ->numeric(),

                Forms\Components\Toggle::make('ativo')
                    ->columnSpan(1)
                    ->required()
                    ->inline(false)
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with('creator', 'updater');
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo_vinculo')
                    ->label('Vínculo'),

                Tables\Columns\TextColumn::make('tipo_documento'),

                Tables\Columns\TextColumn::make('nro_documento')
                    ->searchable(),

                Tables\Columns\TextColumn::make('inscricao_estadual'),

                Tables\Columns\ToggleColumn::make('ativo')
                    ->toggleable()
                    ->beforeStateUpdated(function ($record, $state) {
                        $record->update(['status' => !$state]);
                    }),

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

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Exluído Em')
                    ->dateTime('d/m/Y h:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('tipo_vinculo')
                    ->options(VinculoParceiroEnum::class)
                    ->label('Vínculo'),
                Tables\Filters\SelectFilter::make('ativo')
                    ->options([
                        true => 'Ativo',
                        false => 'Inativo'
                    ])
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([])
            ])
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->slideOver()
                    ->label('Filtros')
            )
            ->paginated([10, 25, 50, 100])
            ->striped();
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
