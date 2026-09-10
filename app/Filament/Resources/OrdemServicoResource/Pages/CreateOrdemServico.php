<?php

namespace App\Filament\Resources\OrdemServicoResource\Pages;

use App\Enums\PrioridadeOrdemServicoEnum;
use App\Enums\StatusOrdemServicoEnum;
use App\Enums\StatusProcessoOrdemServicoEnum;
use App\Filament\Resources\OrdemServicoResource;
use App\Services\OrdemServicoAuditService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreateOrdemServico extends CreateRecord
{
    protected static string $resource = OrdemServicoResource::class;

    protected static ?string $title = 'Nova Ordem';

    protected array $novaOsContext = [];

    public function mount(): void
    {
        parent::mount();

        $this->novaOsContext = Session::pull('nova_os_context', []);

        if ($this->novaOsContext !== []) {
            $this->form->fill(array_merge($this->form->getState(), [
                'parceiro_id' => $this->novaOsContext['parceiro_id'] ?? null,
                'nro_doc_parceiro' => $this->novaOsContext['nro_doc_parceiro'] ?? null,
                'data_ordem' => $this->novaOsContext['data_ordem'] ?? today()->toDateString(),
            ]));
        }

        OrdemServicoAuditService::record(
            event: 'create_form_opened',
            source: 'filament.create',
            newDataOrdem: $this->form->getState()['data_ordem'] ?? null,
            context: [
                'session_context' => $this->novaOsContext ?: null,
                'expected_today' => today()->toDateString(),
            ],
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        OrdemServicoAuditService::record(
            event: 'create_submitted',
            source: 'filament.create',
            newDataOrdem: $data['data_ordem'] ?? null,
            context: [
                'session_context' => $this->novaOsContext ?: null,
                'expected_today' => today()->toDateString(),
            ],
        );

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['status'] = StatusOrdemServicoEnum::PENDENTE;
        $data['status_processo'] = StatusProcessoOrdemServicoEnum::PENDENTE->value;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        OrdemServicoAuditService::record(
            event: 'create_completed',
            ordemServico: $this->record,
            source: 'filament.create',
            newDataOrdem: $this->record?->getRawOriginal('data_ordem'),
            context: [
                'session_context' => $this->novaOsContext ?: null,
            ],
        );
    }

}
