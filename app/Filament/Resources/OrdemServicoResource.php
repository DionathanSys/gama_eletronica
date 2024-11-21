<?php

namespace App\Filament\Resources;

use App\Actions\Fatura\CreateFaturaAction;
use App\Enums\PrioridadeOrdemServicoEnum;
use App\Filament\Resources\OrdemServicoResource\Pages;
use App\Filament\Resources\OrdemServicoResource\RelationManagers;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\ItensOrcamentoRelationManager;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\ItensOrdensAnterioresRelationManager;
use App\Filament\Resources\OrdemServicoResource\RelationManagers\ItensRelationManager;
use App\Actions\Fiscal\CreateNfRetornoAction;
use App\Models\Equipamento;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Models\Veiculo;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
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
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Geral')
                            ->columns(10)
                            ->schema([
                                static::getParceiroFormField(),
                                static::getEquipamentoFormField(),
                                static::getVeiculoFormField(),
                                static::getDataOrdemFormField(),
                                static::getPrioridadeFormField(),
                                static::getStatusFormField(),
                                static::getFaturaFormField(),
                                static::getRelatoClienteFormField(),
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
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Com NF-e Remessa')
                    ->placeholder('Todas')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('nota_entrada_id'),
                        false: fn (Builder $query) => $query->whereNull('nota_entrada_id'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
            ])
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
                    Tables\Actions\BulkAction::make('nf_retorno')
                        ->label('Emitir NF-e Retorno')
                        ->action(function(Collection $record){
                            $notaRetorno = (new CreateNfRetornoAction($record))->exec();
                            
                            if ($notaRetorno){
                                sleep(3);
                                return redirect()->route('nfe.preview.pdf', ['chave' => $notaRetorno->chave]);
                            }
                        }),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('parceiro.nome')
                    ->collapsible(),

            ])
            ->defaultGroup('parceiro.nome')
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

    public static function getParceiroFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('parceiro_id')
                    ->columnSpan(4)
                    ->label('Parceiro')
                    ->relationship('parceiro', 'nome')
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Parceiro::where('tipo_vinculo', 'CLIENTE')
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
                    ->columnSpan(5)
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
                        $data['nro_serie'] = $data['nro_serie'] ?? $data['parceiro_id'] . 'SN' . now()->timestamp;
                        $equipamento = Equipamento::create($data);
                        return $equipamento->id;
                    });
    }

    public static function getVeiculoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('placa')      
                    ->columnSpan(2)
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
                ->columnSpan(2)
                ->disabled();
    } 

    public static function getPrioridadeFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('prioridade')
            ->columnSpan(2)
            ->options(PrioridadeOrdemServicoEnum::class)
            ->default(PrioridadeOrdemServicoEnum::BAIXA);
    }
    
    public static function getFaturaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('fatura_id')
                ->label('Fatura')
                ->columnSpan(1)
                ->disabled();
    } 

    public static function getRelatoClienteFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('relato_cliente')
                ->columnSpanFull();
    } 

    public static function getImageEquipamentoFormFiel(): Forms\Components\FileUpload 
    {
        return Forms\Components\FileUpload::make('img_equipamento')
            ->label('Imagens')
            ->image()
            ->columnSpanFull()
            ->multiple()
            ->panelLayout('grid')
            ->openable();
    }
}
