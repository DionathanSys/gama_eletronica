<?php

namespace App\Traits;

use App\Models\NumeroNotaSaida;

trait ControleNumeracaoNf
{

    public static function getNextNumber(string $serie)
    {
        $nroNotaAtual = NumeroNotaSaida::where('serie_nota', $serie)->max('nro_nota');

        $nextNumber = $nroNotaAtual ? $nroNotaAtual + 1 : 1;

        return $nextNumber;
    }

    public static function setLastNumber(int $lastNumber, string $serie): bool
    {
        $resp = NumeroNotaSaida::create([
            'nro_nota'      => $lastNumber,
            'serie_nota'    => $serie,
        ]);

        if (! $resp) {
            return false;
        }

        return true;

    }
}
