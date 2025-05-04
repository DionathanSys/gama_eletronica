<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemNotaSaida extends Model
{
    protected $table = 'itens_nota_saida';

    protected $casts = [
        'impostos' => 'array',
    ];

    public function notaSaida()
    {
        return $this->belongsTo(NotaSaida::class, 'nota_saida_id');
    }
}
