<?php

use App\Models\Parceiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns parceiros payload for migration api', function () {
    $user = User::factory()->create();

    $parceiro = Parceiro::factory()->create([
        'nome' => 'CLIENTE TESTE',
        'tipo_vinculo' => 'CLIENTE',
        'tipo_documento' => 'CNPJ',
        'nro_documento' => '12345678901234',
        'ativo' => true,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $response = $this->getJson('/api/migracao/parceiros?limit=10');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.legacy_id', $parceiro->id)
        ->assertJsonPath('data.0.nome', 'CLIENTE TESTE')
        ->assertJsonPath('data.0.nro_documento', '12345678901234')
        ->assertJsonPath('meta.resource', 'parceiros')
        ->assertJsonPath('meta.count', 1)
        ->assertJsonPath('meta.has_more', false);
});
