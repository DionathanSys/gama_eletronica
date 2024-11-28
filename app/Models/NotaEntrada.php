<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaEntrada extends Model
{
    protected $table = 'notas_entrada';

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function itensRemessa():HasMany
    {
        return $this->hasMany(ItemNotaRemessa::class);
    }
}
