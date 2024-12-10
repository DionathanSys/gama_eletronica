<?php

namespace App\Filament\Resources;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\VinculoParceiroEnum;
use App\Filament\Resources\NotaSaidaResource\Pages;
use App\Filament\Resources\NotaSaidaResource\RelationManagers;
use App\Filament\Resources\NotaSaidaResource\RelationManagers\OrdensServicoRelationManager;
use App\Models\NotaSaida;
use App\Models\Parceiro;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotaSaidaResource extends Resource
{
    protected static ?string $model = NotaSaida::class;

    protected static ?string $navigationGroup = 'Fiscal';

    protected static ?string $pluralModelLabel = 'Notas de Saída';

    protected static ?string $navigationLabel = 'Notas de Saída';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Geral')
                            ->schema([
                                OrdemServicoResource::getParceiroFormField()
                                    ->columnSpan(3),

                                OrdemServicoResource::getNroDocParceiroFormField(),

                                static::getNaturezaOperacaoFormField(),

                                static::getDataEmissaoFormField(),
                            ]),
                        Tab::make('Transporte')
                            ->schema([
                                Builder::make('frete')
                                    ->label('Transporte')
                                    ->blocks([
                                        Block::make('frete')
                                            ->schema([
                                                static::getTransportadoraFormField(),
                                                static::getModalidadeFreteFormField(),
                                                static::getVolumeEspecieFormField(),
                                                static::getVolumeQuantidadeFormField(),
                                                static::getVolumePesoLiquidoFormField(),
                                                static::getVolumePesoBrutoFormField(),
                                            ])
                                    ])
                            ]),
                        Tab::make('notas')
                            ->schema([
                                static::getNotasReferenciadasFormField()
                                    ->label(''),
                            ])
                        ]
                    )

            ]);
        // Forms\Components\Select::make('parceiro_id')
        //     ->relationship('parceiro', 'nome')
        //     ->required(),

        // Forms\Components\TextInput::make('chave_nota'),
        // Forms\Components\TextInput::make('nro_nota')
        //     ->numeric(),

        // Forms\Components\TextInput::make('serie')
        //     ->numeric(),

        // Forms\Components\DatePicker::make('data_emissao'),
        // Forms\Components\DatePicker::make('data_entrada_saida'),
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('parceiro.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fatura_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('natureza_operacao')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chave_nota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_nota')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serie')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('data_emissao')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_entrada_saida')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdensServicoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotaSaidas::route('/'),
            'create' => Pages\CreateNotaSaida::route('/create'),
            'edit' => Pages\EditNotaSaida::route('/{record}/edit'),
        ];
    }

    public static function getNaturezaOperacaoFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('natureza_operacao')
            ->label('Natureza Operação')
            ->columnSpan(3)
            ->options(
                collect(NaturezaOperacaoEnum::cases())
                    ->mapWithKeys(fn($natureza) => [$natureza->value => $natureza->description()])
                    ->toArray()
            )
            ->required()
            ->disabled();
    }

    public static function getDataEmissaoFormField(): Forms\Components\DatePicker
    {
        return Forms\Components\DatePicker::make('data_emissao')
            ->label('Data Emissão')
            ->columnSpan(2)
            ->native(false)
            ->displayFormat('d/m/Y')
            ->disabled();
    }

    public static function getNotasReferenciadasFormField(): Forms\Components\KeyValue
    {
        return Forms\Components\KeyValue::make('notas_referenciadas')
            ->columnSpan(6);
    }

    public static function getTransportadoraFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('transportadora_id')
            ->label('Transportadora')
            ->columnSpan(6)
            ->relationship('parceiro', 'nome')
            ->preload()
            ->searchable()
            ->hint('Não obrigatório')
            ->options(function () {
                return Parceiro::where('tipo_vinculo', VinculoParceiroEnum::TRANSPORTADORA)
                    ->where('ativo', true)
                    ->pluck('nome', 'id');
            });
    }

    public static function getModalidadeFreteFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('modalidade_frete')
            ->columnSpan(6)
            ->options([
                '0' => "0 - por conta do emitente",
                '1' => "1 - por conta do destinatário",
                '2' => "2 - por conta de terceiros",
                '3' => "3 - Transporte Proprio por conta do Remetente",
                '4' => "4 - Transporte Proprio por conta do Destinatario",
                '9' => "9 - sem frete"
            ])
            ->default(1);
    }

    public static function getVolumeQuantidadeFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('volume_quantidade')
            ->columnSpan(2)
            ->default(1)
            ->minValue(1)
            ->numeric();
    }

    public static function getVolumePesoLiquidoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('volume_peso_liquido')
            ->columnSpan(2)
            ->default(1)
            ->minValue(1)
            ->numeric();
    }

    public static function getVolumePesoBrutoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('volume_peso_bruto')
            ->columnSpan(2)
            ->default(1)
            ->minValue(1)
            ->numeric();
    }

    public static function getVolumeEspecieFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('volume_especie')
            ->columnSpan(3)
            ->label('Espécie dos Volumes')
            ->options([
                'CAIXA' => 'Caixa',
                'PACOTE' => 'Pacote',
                'VOLUME' => 'Volume',
                'ROLO' => 'Rolo',
                'TAMBOR' => 'Tambor',
                'FARDO' => 'Fardo',
                'SACO' => 'Saco',
                'PALLET' => 'Pallet',
                'CONTAINER' => 'Container',
                'ENVELOPE' => 'Envelope',
            ])
            ->searchable();
    }
}