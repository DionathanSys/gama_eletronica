<?php

namespace App\Filament\Resources;

use App\Actions\Fatura\CreateFaturaAction;
use App\Filament\Resources\OrdemServicoResource\Pages;
use App\Filament\Resources\OrdemServicoResource\RelationManagers;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\ItensOrdensAnterioresRelationManager;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\ItensRelationManager;
use App\Models\Equipamento;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OrdemServicoResource extends Resource
{
    protected static ?string $model = OrdemServico::class;

    protected static ?string $pluralModelLabel = 'Ordens de Serviço';

    protected static ?string $navigationLabel = 'Ordem de Serviço';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(5)
            ->schema([
                static::getParceiroFormField(),
                static::getEquipamentoFormField(),
                static::getVeiculoFormField(),
                static::getDataOrdemFormField(),
                static::getStatusFormField(),
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nro. OS')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->label('Cliente')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('equipamento.descricao')
                    ->label('Descrição')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('equipamento.nro_serie')
                    ->label('Nro. Série')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('veiculo.placa')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fatura_id')
                    ->label('Fatura')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('data_ordem')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('desconto')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime('d/m/Y')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('faturar')
                        ->action(function(Collection $record){
                            $fatura = CreateFaturaAction::exec($record);
                            if ($fatura){
                                return redirect(FaturaResource::getUrl('edit', ['record' => $fatura->id,]));
                            }
                        }),
                ]),
            ])
            ->poll('5s');
    }

    public static function getRelations(): array
    {
        return [
            ItensRelationManager::class,
            ItensOrdensAnterioresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdemServicos::route('/'),
            'create' => Pages\CreateOrdemServico::route('/create'),
            'edit' => Pages\EditOrdemServico::route('/{record}/edit'),
        ];
    }

    public static function getParceiroFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parceiro_id')
                    ->columnSpan(2)
                    ->label('Parceiro')
                    ->relationship('parceiro', 'nome')
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Parceiro::where('tipo_vinculo', 'cliente')
                                        ->where('ativo', true)
                                        ->pluck('nome', 'id');
                    })
                    ->afterStateUpdated(function(Set $set){
                        $set('equipamento_id', null);
                        $set('veiculo_id', null);
                    })
                    ->live()
                    ->required();
    }

    public static function getDataOrdemFormField(): Forms\Components\DatePicker
    {
        return  Forms\Components\DatePicker::make('data_ordem')
                    ->columnSpan(1)
                    ->date('d/m/Y')
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->maxDate(now())
                    ->native(false)
                    ->required()
                    ->default(now());
    }

    public static function getEquipamentoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('equipamento_id')
                    ->columnSpan(2)
                    ->label('Equipamento')
                    ->placeholder('Equipamento')
                    ->relationship('equipamento', 'descricao')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->options(function (Forms\Get $get) {
                        return Equipamento::where('parceiro_id', $get('parceiro_id'))
                                        ->pluck('descricao_nro_serie', 'id');
                    })
                    ->createOptionForm(function(Form $form){
                        return $form->columns(5)->schema([
                            EquipamentoResource::getDescricaoFormField()->columnSpan(2),
                            EquipamentoResource::getNroSerieFormField(),
                            EquipamentoResource::getModeloFormField(),
                            EquipamentoResource::getMarcaFormField(),
                        ]);
                    }
                    )
                    ->createOptionUsing(function (array $data, Forms\Get $get): int {
                        $data['parceiro_id'] = $get('parceiro_id') ?? null;
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                        $equipamento = Equipamento::create($data);
                        return $equipamento->id;
                    });
    }

    public static function getVeiculoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('veiculo_id')      
                    ->columnSpan(1)
                    ->label('Veículo')
                    ->relationship('veiculo', 'placa')
                    ->placeholder('Placa')
                    ->preload()
                    ->searchable()
                    ->options(function (Forms\Get $get) {
                        return Equipamento::where('parceiro_id', $get('parceiro_id'))
                                        ->pluck('descricao', 'id');
                    })
                    ->createOptionForm(function(Form $form){
                        return $form->columns(5)->schema([
                            EquipamentoResource::getDescricaoFormField()->columnSpan(2),
                        ]);
                    }
                    )
                    ->createOptionUsing(function (array $data, Forms\Get $get): int {
                        $data['parceiro_id'] = $get('parceiro_id') ?? null;
                        $data['created_by'] = Auth::id();
                        $data['updated_by'] = Auth::id();
                        $equipamento = Equipamento::create($data);
                        return $equipamento->id;
                    });
    }

    public static function getDescontoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('desconto')
                ->columnSpan(1)
                ->prefix('%')
                ->numeric()
                ->default(0);
    } 
    
    public static function getStatusFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('status')
                ->columnSpan(1)
                ->disabled();
    } 
    
}
