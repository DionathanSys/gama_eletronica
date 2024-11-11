<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fatura extends Model
{
     /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): HasOne
    {
        return $this->hasOne(Parceiro::class);
    }
}
