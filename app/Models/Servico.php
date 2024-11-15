<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Servico extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function itens_ordem_servico(): HasMany
    {
        return $this->hasMany(ItemOrdemServico::class);
    }

    public function impostos()
    {
        return $this->hasMany(ImpostoServico::class, 'id', 'imposto_servico_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = strtoupper(Str::ascii($value));
    }

}
