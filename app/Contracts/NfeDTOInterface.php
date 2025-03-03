<?php

namespace App\Contracts;

interface NfeDTOInterface
{
    public static function fromNotaSaida(\App\Models\NotaSaida $notaSaida): self;
    public function toArray(): array;
    public function getNumero(): int;
    public function getSerie(): int;
    public function getDataEmissao(): string;
    public function getDataEntradaSaida(): string;
}