<?php

namespace App\Models;

use App\Enums\NaturezaOperacaoEnum;
use App\Enums\VinculoParceiroEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use function Pest\Laravel\put;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    protected $casts = [
        'img_equipamento' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function userCreate()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function parceiro(): BelongsTo
    {
        return $this->BelongsTo(Parceiro::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Parceiro::class, 'parceiro_id')
                    ->where('tipo_vinculo', VinculoParceiroEnum::CLIENTE);
    }

    public function equipamento(): BelongsTo
    {
        return $this->BelongsTo(Equipamento::class);
    }

    public function veiculo():BelongsTo
    {
        return $this->BelongsTo(Veiculo::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemOrdemServico::class);
    }
    
    public function itens_orcamento(): HasMany
    {
        return $this->hasMany(Orcamento::class);
    }

    public function itensOrdensAnteriores()
    {
        return $this->hasManyThrough(ItemOrdemServico::class, OrdemServico::class, 'equipamento_id', 'ordem_servico_id', 'equipamento_id', 'id')
                    ->where('ordens_servico.data_ordem', '<', $this->data_ordem);

    }

    public function itemNotaRemessa(): HasOne
    {
        return $this->HasOne(ItemNotaRemessa::class);
    }

    public function notaEntrada()
    {
        return $this->hasOneThrough(
            NotaEntrada::class,         //Modelo que deve retornar
            ItemNotaRemessa::class,     //Modelo que realiza a ligação
            'ordem_servico_id',         //Chave na tabela de ligação
            'id',                       //Chave que relaciona a tabela fim com a de ligação
            'id',                       //Chave PAI na tabela atual
            'nota_entrada_id');         //chave que relaciona a tabela ligação com a fim
    }

    public function notasSaida(): BelongsToMany
    {
        return $this->belongsToMany(
            NotaSaida::class,
            'nota_saida_ordem_servico',
            'ordem_servico_id',
            'nota_saida_id',
        );
    }

    public function notaRetorno(): BelongsToMany
    {
        return $this->belongsToMany(
            NotaSaida::class,
            'nota_saida_ordem_servico',
            'ordem_servico_id',
            'nota_saida_id',
        )->where('natureza_op', NaturezaOperacaoEnum::RETORNO_MERCADORIA->value);
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */

    public function setRelatoClienteAttribute($value)
    {
        $this->attributes['relato_cliente'] = strtoupper($value);
    }
    public function setObservacaoGeralAttribute($value)
    {
        $this->attributes['observacao_geral'] = strtoupper($value);
    }
    public function setObservacaoInternaAttribute($value)
    {
        $this->attributes['observacao_interna'] = strtoupper($value);
    }
    public function setItensRecebidosAttribute($value)
    {
        $this->attributes['itens_recebidos'] = strtoupper($value);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function toPdf(): string|false
    {
        return false;
    }

    public function getDataFormated()
    {
        // Definindo a data
        $date = Carbon::createFromFormat('Y-m-d', $this->data_ordem);

        // Formatando a data no formato desejado
        $formattedDate = $date->locale('pt_BR')->isoFormat('dddd, DD [de] MMMM [de] YYYY');

        // Colocando em maiúsculas
        $formattedDate = strtoupper($formattedDate);

        // Exibindo a data
        return 'CHAPECÓ, ' . $formattedDate;

    }
}
