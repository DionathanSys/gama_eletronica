<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipamento extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): BelongsTo
    {
        return $this->belongsTo(Parceiro::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

        /*
    |--------------------------------------------------------------------------
    | Atributos
    |--------------------------------------------------------------------------
    */

    public function setDescricaoAttribute($value)
    {
        $this->attributes['descricao'] = strtoupper($value);
    }
  
    public function setMarcaAttribute($value)
    {
        $this->attributes['marca'] = strtoupper($value);
    }
  
    public function setModeloAttribute($value)
    {
        $this->attributes['modelo'] = strtoupper($value);
    }

}
