<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditoriasRelationManager extends RelationManager
{
    protected static string $relationship = 'auditorias';

    protected static ?string $title = 'Auditoria';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('event')->readOnly(),
                Forms\Components\TextInput::make('source')->readOnly(),
                Forms\Components\TextInput::make('old_data_ordem')->readOnly(),
                Forms\Components\TextInput::make('new_data_ordem')->readOnly(),
                Forms\Components\Textarea::make('context')->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origem')
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário'),
                Tables\Columns\TextColumn::make('old_data_ordem')
                    ->label('Data anterior')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('new_data_ordem')
                    ->label('Data nova')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('request_id')
                    ->label('Request ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('context')
                    ->label('Contexto')
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : $state)
                    ->wrap()
                    ->tooltip(fn ($record) => is_array($record->context)
                        ? json_encode($record->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : $record->context)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}
