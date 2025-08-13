<?php

namespace App\Filament\Resources\OrdemServicoResource\RelationManagers;

use App\Actions\OrdemServico\UpdateValorOrdemActions;
use App\Enums\StatusOrdemServicoEnum;
use App\Enums\StatusProcessoOrdemServicoEnum;
use App\Traits\UpdateStatusProcessoOrdemServico;
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

class ItensOrcamentoRelationManager extends RelationManager
{
    use UpdateStatusProcessoOrdemServico;

    protected static string $relationship = 'itens_orcamento';

    protected static ?string $title = 'Orçamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ItensRelationManager::getServicoFormField(),
                ItensRelationManager::getQuantidadeFormField(),
                ItensRelationManager::getValorUnitarioFormField(),
                ItensRelationManager::getValorTotalFormField(),
                ItensRelationManager::getObservacaoFormField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->formatStateUsing(fn($state)=> str_pad($state, 5, '0', STR_PAD_LEFT)),

                Tables\Columns\TextColumn::make('servico.nome')
                    ->label('Serviço'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->numeric(2 , ',', '.'),

                Tables\Columns\TextColumn::make('valor_unitario')
                    ->label('Valor Unitário')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->summarize(Sum::make()->money('BRL')->label('Total')),

                Tables\Columns\TextColumn::make('observacao')
                    ->words(10)
                    ->tooltip(fn($record)=> $record->observacao)
                    ->label('Observação')
                    ->wrap(),

                Tables\Columns\IconColumn::make('aprovado')
                    ->boolean(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Adicionar Serviço')
                    ->label('Serviço')
                    ->icon('heroicon-o-plus')
                    ->color('info')
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false)
                    ->mutateFormDataUsing(function (array $data): array {

                        $this->updateStatusOrdemServico($this->getOwnerRecord(), StatusProcessoOrdemServicoEnum::AGUARDANDO_APROVACAO->value);

                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false),

                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(fn()=>$this->getOwnerRecord()->status == StatusOrdemServicoEnum::PENDENTE->value ? true : false),

            ])
            ->bulkActions([
            ])
            ->poll('5s');
    }
}
