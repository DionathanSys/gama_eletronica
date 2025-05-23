<?php

namespace App\Models;

use App\Enums\VinculoParceiroEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parceiro extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'tipo_vinculo' => VinculoParceiroEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }

    public function endereco(): HasOne
    {
        return $this->hasOne(Endereco::class);
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

    public function ordensServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class);
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class);
    }

    public function contasReceber(): HasMany
    {
        return $this->hasMany(ContaReceber::class);
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

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = strtoupper($value);
    }

    public function setNroDocumentoAttribute($value)
    {
        $this->attributes['nro_documento'] = preg_replace('/[-\/\.]/', '', $value);
    }

    public function getNroDocumentoFormatadoAttribute(): string
    {
        return preg_replace(
            '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
            '$1.$2.$3/$4-$5',
            $this->nro_documento
        );
    }
}
