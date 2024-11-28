<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemNotaRemessa extends Model
{
    protected $table = 'itens_nota_remessa';

    public function ordemServico(): BelongsTo
    {
        return $this->BelongsTo(OrdemServico::class);
    }

    public function notaEntrada():BelongsTo
    {
        return $this->belongsTo(NotaEntrada::class, 'nota_entrada_id', 'id');
    }
}
