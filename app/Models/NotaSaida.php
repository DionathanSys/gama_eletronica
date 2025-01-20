<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaSaida extends Model
{
    protected $table = 'notas_saida';

    protected $casts = [
        'notas_referenciadas' => 'array',
        'frete' => 'array',
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
}
