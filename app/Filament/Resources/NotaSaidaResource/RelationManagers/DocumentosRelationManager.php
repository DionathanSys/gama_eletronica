<?php

namespace App\Filament\Resources\NotaSaidaResource\RelationManagers;

use App\Jobs\ConsultaNfJob;
use App\Models\Documento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentosRelationManager extends RelationManager
{
    protected static string $relationship = 'documentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('descricao')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->columns([
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('consultar_nfe')
                    ->label('Atualizar Docs.')
                    ->visible(fn() => $this->ownerRecord->documentos->isEmpty())
                    ->action(fn() => ConsultaNfJob::dispatch($this->ownerRecord->chave_nota, 1)->delay(now()->addSeconds(3))),
            ])
            ->actions([
                Tables\Actions\Action::make('download_doc')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->tooltip('Download')
                    ->label('')
                    ->action(function (Documento $record) {

                        return response()->download($record->path);

                        })

            ])
            ->bulkActions([
            ])
            ->poll(3);
    }
}
