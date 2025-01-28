<?php

namespace App\Filament\Resources;

use App\Actions\Fatura\CreateFaturaAction;
use App\Actions\Fiscal\CreateNfeRetornoAction;
use App\Enums\PrioridadeOrdemServicoEnum;
use App\Filament\Resources\OrdemServicoResource\Pages;
use App\Filament\Resources\OrdemServicoResource\RelationManagers;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\{ItensOrcamentoRelationManager, ItensOrdensAnterioresRelationManager, ItensRelationManager};
use App\Actions\Fiscal\CreateNfRetornoAction;
use App\Enums\{StatusProcessoOrdemServicoEnum, TipoManutencaoOrdemServicoEnum, VinculoParceiroEnum};
use App\Models\{Equipamento, OrdemServico, Parceiro, Veiculo};
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\{Section, Select, Tabs, TextInput};
use Filament\Forms\{Form, Get, Set};
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OrdemServicoResource extends Resource
{
    protected static ?string $model = OrdemServico::class;

    protected static ?string $pluralModelLabel = 'Ordens de Serviço';

    protected static ?string $navigationLabel = 'Ordem de Serviço';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Geral')
                            ->columns(10)
                            ->schema([
                                Section::make('Principal')
                                    ->columns(12)
                                    ->schema([
                                        static::getIdFormField(),
                                        static::getFaturaFormField(),
                                        static::getDataOrdemFormField(),
                                        static::getStatusProcessoFormField(),
                                        static::getTipoManutencaoFormField(),
                                        static::getPrioridadeFormField(),
                                    ]),
                                Section::make('Info Cliente')
                                    ->columns(12)
                                    ->schema([
                                        static::getParceiroFormField(),
                                        static::getNroDocParceiroFormField(),
                                        static::getEquipamentoFormField(),
                                        static::getVeiculoFormField(),
                                    ]),
                                Section::make('Observações')
                                    ->columns(12)
                                    ->schema([
                                        static::getRelatoClienteFormField(),
                                        static::getItensRecebidosFormField(),
                                        static::getObsGeralFormField(),
                                        static::getObsInternaFormField(),
                                    ]),
                                static::getDescontoOrdemFormField(),
                                static::getStatusFormField(),
                            ]),
                        Tabs\Tab::make('Anexos')
                            ->schema([
                                static::getImageEquipamentoFormFiel(),
                            ]),
                        
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Nro. OS')
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->label('Cliente')
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Thin)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),

                Tables\Columns\TextColumn::make('equipamento.descricao')
                    ->label('Equipamento')
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Thin)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),
                
                Tables\Columns\TextColumn::make('equipamento.nro_serie')
                    ->label('Nro. Série')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Thin)
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),

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
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status_processo')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('relato_cliente')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('itens_recebidos')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('observacao_geral')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('observacao_interna')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('userCreate.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Criado Por'),
                
                Tables\Columns\TextColumn::make('userUpdate.name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Atualizado Por'),

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
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('nota_entrada_id')
                    ->label('Com NF-e Remessa')
                    ->placeholder('Todas')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('nota_entrada_id'),
                        false: fn (Builder $query) => $query->whereNull('nota_entrada_id'),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('parceiro_id')
                    ->label('Cliente')
                    ->searchable()
                    ->relationship('parceiro', 'nome')
            ])
            ->persistFiltersInSession()
            ->deselectAllRecordsWhenFiltered(false)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
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
                
                    Tables\Actions\BulkAction::make('nfe_retorno')
                        ->label('Emitir NF-e Retorno')
                        ->requiresConfirmation()
                        ->action(function(Collection $record){
                            $notas = $record
                                        ->map(fn($ordem) => $ordem->notaEntrada ? [
                                            'chave_nota' => $ordem->notaEntrada->chave_nota,
                                            'data_fatura' => $ordem->notaEntrada->data_fatura,
                                            'nro_nota' => $ordem->notaEntrada->nro_nota,
                                        ] : null) // Obter os campos do relacionamento 'notaEntrada'
                                        ->filter() // Remover valores nulos
                                        ->unique(fn($nota) => $nota['chave_nota']) // Remover duplicados com base em 'chave_nota'
                                        ->map(fn($nota) => [
                                            $nota['chave_nota'] => "Nro. {$nota['nro_nota']} - {$nota['data_fatura']}",
                                        ]) // Reformatar os valores
                                        ->values() // Reindexar a collection
                                        ->collapse()
                                        ->toArray();
                            
                            $notaSaida = CreateNfeRetornoAction::prepare($record, $notas);

                            if (! $notaSaida){
                                return false;
                            }
                            
                            return redirect(NotaSaidaResource::getUrl('edit', ['record' => $notaSaida->id]));
                        })
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('parceiro.nome')
                    ->collapsible(),

            ])
            // ->defaultGroup('parceiro.nome')
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->slideOver()
                    ->label('Filtros')
            )
            ->paginated([10, 25, 50, 100])
            ->striped()
            ->poll('5s');
    }

    public static function getRelations(): array
    {
        return [
            ItensRelationManager::class,
            ItensOrcamentoRelationManager::class,
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

    public static function getIdFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('id')
                ->label('Nro. Ordem')
                ->readOnly(fn()=>Auth::user()->id == 1 ? false : true)
                ->formatStateUsing(fn($state)=> $state ? str_pad($state, 5, '0', STR_PAD_LEFT) : '' )
                ->columnSpan(2);
    } 

    public static function getParceiroFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parceiro_id')
                    ->columnSpan(9)
                    ->label('Parceiro')
                    ->relationship('cliente', 'nome')
                    ->preload()
                    ->searchable()
                    ->default(fn() => Session::get('parceiro_id', null))
                    ->afterStateUpdated(function(Set $set, Get $get, $state){
                        
                        $set('equipamento_id', null);
                        $set('veiculo_id', null);
                        
                        if ($state){
                            $set('nro_doc_parceiro', Parceiro::find($get('parceiro_id'))->nro_documento ?? '');
                        }

                    })
                    ->live(onBlur: true)
                    ->required();
    }

    public static function getNroDocParceiroFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('nro_doc_parceiro')
                    ->columnSpan(3)
                    ->label('CNPJ/CPF')
                    ->placeholder('CPF/CNPJ')
                    ->default(Session::get('nro_doc_parceiro', null))
                    ->dehydrated(false);
                    
    }

    public static function getDataOrdemFormField(): Forms\Components\DatePicker
    {
        return  Forms\Components\DatePicker::make('data_ordem')
                    ->columnSpan(2)
                    ->date('d/m/Y')
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection()
                    ->maxDate(now())
                    ->native(false)
                    ->required()
                    ->default(Session::get('data_ordem', now()));
    }

    public static function getEquipamentoFormField($var= null): Forms\Components\Select
    {
        return Forms\Components\Select::make('equipamento_id')
                    ->columnSpan(9)
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
                    ->hintActions([
                        Forms\Components\Actions\Action::make('equipamento')
                            ->icon('heroicon-o-pencil-square')
                            ->color('info')
                            ->url(function(Forms\Get $get){
                                if($get('equipamento_id')){
                                    return EquipamentoResource::getUrl('edit', ['record' => $get('equipamento_id')]);
                                }
                                return EquipamentoResource::getUrl();
                            })
                            ->openUrlInNewTab()
                    ])
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
                        $data['nro_serie'] = $data['nro_serie'] ?? 'SN';
                        $equipamento = Equipamento::create($data);
                        return $equipamento->id;
                    });
    }

    public static function getVeiculoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('placa')      
                    ->columnSpan(3)
                    ->length(7)
                    ->placeholder('Placa');
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
                ->visible(fn()=>Auth::user()->id == 1 ? true : false)
                ->columnSpan(2);
    } 
    
    public static function getStatusProcessoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('status_processo')
                ->columnSpan(2)
                ->options(
                    collect(StatusProcessoOrdemServicoEnum::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->getStatus()])
                        ->toArray()
                )
                ->default(StatusProcessoOrdemServicoEnum::PENDENTE->value);
    } 

    public static function getPrioridadeFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('prioridade')
            ->columnSpan(2)
            ->options(PrioridadeOrdemServicoEnum::class)
            ->default(PrioridadeOrdemServicoEnum::BAIXA);
    }
    
    public static function getTipoManutencaoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('tipo_manutencao')
            ->label('Tipo Manutenção')
            ->columnSpan(2)
            ->options(TipoManutencaoOrdemServicoEnum::class)
            ->default(TipoManutencaoOrdemServicoEnum::CORRETIVA);
    }
    
    public static function getFaturaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('fatura_id')
                ->label('Fatura')
                ->formatStateUsing(fn($state)=> $state ? str_pad($state, 5, '0', STR_PAD_LEFT) : '' )
                ->columnSpan(2)
                ->readOnly();
    } 

    public static function getRelatoClienteFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('relato_cliente')
                ->columnSpan(6);
    } 
    public static function getObsGeralFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('observacao_geral')
                ->label('Observações Gerais')
                ->maxLength(255)
                ->columnSpan(6);
    } 
    public static function getObsInternaFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('observacao_interna')
                ->label('Observações Internas')
                ->maxLength(255)
                ->columnSpan(6);
    } 
    
    public static function getDescontoOrdemFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('desconto')
                ->columnSpan(2)
                ->prefix('R$')
                ->numeric()
                ->default(0)
                ->minValue(0);
        ;

    } 

    public static function getItensRecebidosFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('itens_recebidos')
                ->columnSpan(6);
    } 

    public static function getImageEquipamentoFormFiel(): Forms\Components\FileUpload 
    {
        return Forms\Components\FileUpload::make('img_equipamento')
            ->label('Imagens')
            ->image()
            ->columnSpanFull()
            ->multiple()
            ->panelLayout('grid')
            /* ->openable() */;
    }
}
