<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Services\OrdemServicoAuditService;

class OrdemServicoObserver
{
    public function created(OrdemServico $ordemServico): void
    {
        OrdemServicoAuditService::record(
            event: 'created',
            ordemServico: $ordemServico,
            source: 'model.observer',
            newDataOrdem: $ordemServico->getAttributes()['data_ordem'] ?? null,
            context: [
                'changed_fields' => array_keys($ordemServico->getAttributes()),
            ],
        );
    }

    public function updated(OrdemServico $ordemServico): void
    {
        $changes = $ordemServico->getChanges();

        OrdemServicoAuditService::record(
            event: 'updated',
            ordemServico: $ordemServico,
            source: 'model.observer',
            oldDataOrdem: $ordemServico->getRawOriginal('data_ordem'),
            newDataOrdem: $ordemServico->getAttributes()['data_ordem'] ?? null,
            context: [
                'changed_fields' => array_keys($changes),
                'data_ordem_changed' => array_key_exists('data_ordem', $changes),
            ],
        );
    }

    public function deleted(OrdemServico $ordemServico): void
    {
        OrdemServicoAuditService::record(
            event: 'deleted',
            ordemServico: $ordemServico,
            source: 'model.observer',
            oldDataOrdem: $ordemServico->getRawOriginal('data_ordem'),
            context: [
                'changed_fields' => [],
            ],
        );
    }
}
