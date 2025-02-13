<?php

namespace App\Models;

use App\Enums\StatusNotaFiscalEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NotaSaida extends Model
{
    protected $table = 'notas_saida';

    protected $casts = [
        'status'                =>   StatusNotaFiscalEnum::class,
        'notas_referenciadas'   =>   'array',
        'frete'                 =>   'array',
        'eventos'                 =>   'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): BelongsTo
    {
        return $this->BelongsTo(Parceiro::class);
    }

    public function transportadora(): BelongsTo
    {
        return $this->BelongsTo(Parceiro::class);
    }
   
    public function cliente(): BelongsTo
    {
        return $this->BelongsTo(Parceiro::class, 'parceiro_id');
    }

    public function ordensServico(): BelongsToMany
    {
        return $this->belongsToMany(
            OrdemServico::class,
            'nota_saida_ordem_servico',
            'nota_saida_id',
            'ordem_servico_id',
        );
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
}
