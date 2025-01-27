<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    protected $table = 'contas_receber';

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): BelongsTo
    {
        return $this->belongsTo(Parceiro::class);
    }

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class);
    }

    public function ordensServico(): Attribute
    {
        $idOrdensServico = ($this->fatura->ordensServico->pluck('id'))->toArray();

        $idString = '';
        foreach ($idOrdensServico as $key => $id) {
            $idString .= $id . ' - ';
        }

        return Attribute::make(
            get: fn()=> substr($idString, 0, -2));
    }
}
