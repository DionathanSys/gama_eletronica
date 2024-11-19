<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    protected $casts = [
        'img_equipamento' => 'array',
    ];

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
    
    public function itens_orcamento(): HasMany
    {
        return $this->hasMany(Orcamento::class);
    }

    public function itensOrdensAnteriores()
    {
        return $this->hasManyThrough(ItemOrdemServico::class, OrdemServico::class, 'equipamento_id', 'ordem_servico_id', 'equipamento_id', 'id')
                    ->where('ordens_servico.data_ordem', '<', $this->data_ordem);

    }

    public function notaRemessa(): BelongsTo
    {
        return $this->BelongsTo(NotaEntrada::class, 'nota_entrada_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function toPdf(): string|false
    {
        return false;
    }
}
