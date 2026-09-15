<?php

use App\Filament\Resources\OrdemServicoResource;
use Carbon\Carbon;

it('allows the current order date after midnight', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 15, 14, 30, 0, 'America/Sao_Paulo'));

    try {
        $field = OrdemServicoResource::getDataOrdemFormField();

        expect($field->getMaxDate())
            ->toBe('2026-09-15');

        expect($field->mutateStateForValidation('2026-09-15 14:30:00'))
            ->toBe('2026-09-15');
    } finally {
        Carbon::setTestNow();
    }
});
