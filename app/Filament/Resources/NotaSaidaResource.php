<?php

namespace App\Filament\Resources;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\VinculoParceiroEnum;
use App\Filament\Resources\NotaSaidaResource\Pages;
use App\Filament\Resources\NotaSaidaResource\RelationManagers;
use App\Filament\Resources\NotaSaidaResource\RelationManagers\DocumentosRelationManager;
use App\Filament\Resources\NotaSaidaResource\RelationManagers\ItensRelationManager;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
                            ->columns(12)
                            ->schema([
                                OrdemServicoResource::getParceiroFormField()
                                    ->disabled()
                                    ->columnSpan(6),
                                static::getNaturezaOperacaoFormField()
                                    ->columnSpan(3),

                                static::getDataEmissaoFormField(),
                                static::getChaveNotaFormField(),
                                static::getNroNotaFormField(),
                                static::getSerieNotaFormField(),
                                static::getStatusFormField(),

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
                            ->label('Notas Remessa')
                            ->schema([
                                static::getNotasReferenciadasFormField()
                                    ->label(''),
                            ]),
                        Tab::make('eventos')
                            ->label('Eventos')
                            ->schema([
                                static::getEventosNotaFormField()
                                    ->label(''),

                            ])
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parceiro.nome')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_nota')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serie')
                    ->label('Série')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ordensServico.id')
                    ->label('OS\'s')
                    ->placeholder('N/A')
                    // ->listWithLineBreaks()
                    // ->limitList(1)
                    // ->expandableLimitedList()
                    ->searchable(['ordens_servico.id'])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('fatura_id')
                    ->placeholder('N/A')
                    ->label('Fatura')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chave_nota')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('natureza_operacao')
                    ->label('Natureza Operação')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status'),

                Tables\Columns\TextColumn::make('data_emissao')
                    ->label('Dt. Emissão')
                    ->date()
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('data_entrada_saida')
                    ->label('Dt. Ent. Saí.')
                    ->date()
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado Em')
                    ->dateTime()
                    ->dateTime('d/m/Y h:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado Em')
                    ->dateTime()
                    ->dateTime('d/m/Y h:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parceiro_id')
                    ->label('Cliente')
                    ->searchable()
                    ->relationship('parceiro', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Action::make('view_pdf')
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->disabled(fn($record) => $record->chave_nota ? false : true)
                    ->url(fn(NotaSaida $record) => route('nfe.view.pdf', ['notaSaida' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->paginated([25, 50, 100])
            ->defaultSort('id', 'desc')
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
            ItensRelationManager::class,
            DocumentosRelationManager::class,
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

    public static function getStatusFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('status')
            ->columnSpan(3)
            ->disabled();
    }

    public static function getNotasReferenciadasFormField(): Forms\Components\KeyValue
    {
        return Forms\Components\KeyValue::make('notas_referenciadas')
            ->keyLabel('Chave NF-e')
            ->valueLabel('Descrição')
            ->editableKeys(true)
            ->addable(false)
            ->deletable(false)
            ->columnSpan(6);
    }

    public static function getEventosNotaFormField(): Forms\Components\KeyValue
    {
        return Forms\Components\KeyValue::make('eventos')
            ->keyLabel('Evento')
            ->valueLabel('Descrição')
            ->editableKeys(false)
            ->addable(false)
            ->deletable(false)
            ->columnSpan(6);
    }

    public static function getTransportadoraFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('transportadora_id')
            ->label('Transportadora')
            ->columnSpan(6)
            // ->relationship('parceiro', 'nome')
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
                // '9' => "9 - sem frete"   Removido devido o fato de poder deixar sem criar frete, onde é aplicado o valor 9 automaticamente
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
            ->minValue(0.01)
            ->numeric();
    }

    public static function getVolumePesoBrutoFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('volume_peso_bruto')
            ->columnSpan(2)
            ->default(1)
            ->minValue(0.01)
            ->numeric();
    }

    public static function getChaveNotaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('chave_nota')
            ->columnSpan(5)
            ->readOnly(fn() => !Auth::user()->admin)
            ->numeric();
    }

    public static function getNroNotaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('nro_nota')
            ->label('Nro. Nota')
            ->columnSpan(2)
            ->readOnly(fn() => !Auth::user()->admin)
            ->numeric();
    }

    public static function getSerieNotaFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('serie')
            ->label('Série')
            ->columnSpan(2)
            ->readOnly(fn() => !Auth::user()->admin)
            ->numeric();
    }

    public static function getVolumeEspecieFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('volume_especie')
            ->columnSpan(3)
            ->label('Espécie dos Volumes')
            ->default('CAIXA')
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
