<?php

namespace App\Filament\Resources\FaturaResource\RelationManagers;

use App\Enums\StatusContaReceberEnum;
use App\Enums\StatusFaturaEnum;
use App\Filament\Resources\ContaReceberResource;
use App\Models\Fatura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
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
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->numeric()
                    ->sortable()
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Total')),

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
                    ->badge()
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
                        $data['parceiro_id'] = $this->getOwnerRecord()->parceiro_id;
                        $data['status'] = StatusFaturaEnum::PENDENTE;
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                 
                        return $data;
                    })
                    ->visible(fn()=> $this->getOwnerRecord()->status == StatusFaturaEnum::PENDENTE->value ? true : false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by'] = Auth::id();
                        return $data;
                    })
                    ->visible(fn()=> $this->getOwnerRecord()->status == StatusFaturaEnum::PENDENTE->value ? true : false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn()=> $this->getOwnerRecord()->status == StatusFaturaEnum::PENDENTE->value ? true : false),
                
                Tables\Actions\Action::make('pago')
                    ->icon('heroicon-o-banknotes')
                    ->button()
                    ->color('gray')
                    ->action(fn($record) => $record->update(['status' => StatusContaReceberEnum::PAGO]))
                    ->visible(fn($record) => $record->status == StatusContaReceberEnum::CONFIRMADA->value ? true : false),

                Tables\Actions\Action::make('pgto_pendente')
                    ->label('Pgto. Pendente')
                    ->icon('heroicon-o-banknotes')
                    ->button()  
                    ->color('danger')
                    ->action(function($record){
                        $record->update(['status' => StatusContaReceberEnum::CONFIRMADA]);
                    })
                    ->visible(fn($record) => $record->status == StatusContaReceberEnum::PAGO->value ? true : false),
            ])
            ->bulkActions([
            ]);
    }

}
