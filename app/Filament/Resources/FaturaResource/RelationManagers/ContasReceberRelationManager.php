<?php

namespace App\Filament\Resources\FaturaResource\RelationManagers;

use App\Enums\StatusFaturaEnum;
use App\Filament\Resources\ContaReceberResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ContasReceberRelationManager extends RelationManager
{
    protected static string $relationship = 'contasReceber';

    protected static ?string $title = 'Contas à Receber';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ContaReceberResource::getParceiroFormField(),
                ContaReceberResource::getFaturaFormField(),
                ContaReceberResource::getDataVencimentoFormField(),
                ContaReceberResource::getValorFormField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),

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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Incluir Recebíveis')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status'] = StatusFaturaEnum::PENDENTE;
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
            ]);
    }

}