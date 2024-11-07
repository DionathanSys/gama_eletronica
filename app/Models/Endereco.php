<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): BelongsTo
    {
        return $this->belongsTo(Parceiro::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */

    public function setRuaAttribute($value)
    {
        $this->attributes['rua'] = strtoupper($value);
    }
    
    public function setComplementoAttribute($value)
    {
        $this->attributes['complemento'] = strtoupper($value);
    }
    
    public function setBairroAttribute($value)
    {
        $this->attributes['bairro'] = strtoupper($value);
    }

    public function setCidadeAttribute($value)
    {
        $this->attributes['cidade'] = strtoupper($value);
    }
    
    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = strtoupper($value);
    }
    
    public function setPaisAttribute($value)
    {
        $this->attributes['pais'] = strtoupper($value);
    }
    
}
