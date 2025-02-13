<?php

namespace App\Filament\Resources\NotaSaidaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdensServicoRelationManager extends RelationManager
{
    protected static string $relationship = 'ordensServico';

    protected static ?string $title = 'OS\'s';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
