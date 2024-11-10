<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parceiro extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */
    
    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }

    public function contato(): HasOne
    {
        return $this->hasOne(Contato::class);
    }

    public function equipamentos(): HasMany
    {
        return $this->hasMany(Equipamento::class);
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class);
    }

    public function ordens_servico(): HasMany
    {
        return $this->hasMany(OrdemServico::class);
    }


    /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = strtoupper($value);
    }

    public function setNroDocumentoAttribute($value)
    {
        $this->attributes['nro_documento'] = preg_replace('/[-\/\.]/', '', $value);
    }
}
