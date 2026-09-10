<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoAudit extends Model
{
    protected $table = 'ordem_servico_audits';

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
