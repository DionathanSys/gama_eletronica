<?php

use App\Contracts\NfeDTOInterface;
use App\Enums\StatusNotaFiscalEnum;
use App\Models\NotaSaida;
use App\Services\NfeService;

it('stores only the calendar date when refreshing a nota fiscal', function () {
    $notaSaida = Mockery::mock(NotaSaida::class)->makePartial();
    $notaSaida->id = 244;
    $notaSaida->shouldReceive('update')
        ->once()
        ->with(Mockery::on(function (array $data): bool {
            return $data['data_emissao'] === '2026-09-14'
                && $data['data_entrada_saida'] === '2026-09-14'
                && $data['status'] === StatusNotaFiscalEnum::PROCESSANDO;
        }))
        ->andReturnTrue();

    $dto = Mockery::mock(NfeDTOInterface::class);
    $dto->shouldReceive('getNumero')->once()->andReturn(174);
    $dto->shouldReceive('getSerie')->once()->andReturn(5);
    $dto->shouldReceive('getDataEmissao')->once()->andReturn('2026-09-14T18:00:44-03:00');
    $dto->shouldReceive('getDataEntradaSaida')->once()->andReturn('2026-09-14T18:00:44-03:00');

    $service = (new \ReflectionClass(NfeService::class))->newInstanceWithoutConstructor();
    $method = new \ReflectionMethod(NfeService::class, 'refreshInfoNotaSaida');
    $method->setAccessible(true);

    $method->invoke($service, $notaSaida, $dto, '42260945790457000185550050000001741202630271');
});
