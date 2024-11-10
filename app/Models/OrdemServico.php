<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function parceiro(): BelongsTo
    {
        return $this->BelongsTo(Parceiro::class);
    }

    public function equipamento(): BelongsTo
    {
        return $this->BelongsTo(Equipamento::class);
    }

    public function veiculo():BelongsTo
    {
        return $this->BelongsTo(Veiculo::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemOrdemServico::class);
    }

    public function servicos_anteriores()
    {
        return $this->hasMany(ItemOrdemServico::class,);
    }
}
