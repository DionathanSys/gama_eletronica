<?php

use App\Enums\PrioridadeOrdemServicoEnum;
use App\Enums\VinculoParceiroEnum;
use App\Models\Equipamento;
use App\Models\OrdemServico;
use App\Models\OrdemServicoAudit;
use App\Models\Parceiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    // SQLite used by the tests does not provide MySQL's CONCAT() for this generated column.
    if (Schema::hasColumn('equipamentos', 'descricao_nro_serie')) {
        Schema::table('equipamentos', function ($table) {
            $table->dropColumn('descricao_nro_serie');
        });
    }
});

it('formats the order date without changing the calendar day', function () {
    $ordemServico = new OrdemServico;
    $ordemServico->setRawAttributes([
        'data_ordem' => '2026-09-09',
    ]);

    expect($ordemServico->getDataFormated())
        ->toContain('09 DE SETEMBRO DE 2026');
});

it('audits the original and new order dates', function () {
    $user = User::factory()->create();
    $parceiro = Parceiro::create([
        'nome' => 'Cliente de Teste',
        'tipo_vinculo' => VinculoParceiroEnum::CLIENTE,
        'tipo_documento' => 'CNPJ',
        'nro_documento' => '12345678000199',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);
    $equipamento = Equipamento::create([
        'parceiro_id' => $parceiro->id,
        'descricao' => 'Equipamento de Teste',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $this->actingAs($user);

    $ordemServico = OrdemServico::create([
        'parceiro_id' => $parceiro->id,
        'equipamento_id' => $equipamento->id,
        'data_ordem' => '2026-09-09',
        'prioridade' => PrioridadeOrdemServicoEnum::BAIXA,
        'status' => 'PENDENTE',
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $createdAudit = OrdemServicoAudit::query()
        ->where('ordem_servico_id', $ordemServico->id)
        ->where('event', 'created')
        ->firstOrFail();

    expect($createdAudit->user_id)->toBe($user->id)
        ->and($createdAudit->new_data_ordem)->toBe('2026-09-09');

    $ordemServico->update([
        'data_ordem' => '2026-08-10',
    ]);

    $audit = OrdemServicoAudit::query()
        ->where('ordem_servico_id', $ordemServico->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($audit->user_id)->toBe($user->id)
        ->and($audit->old_data_ordem)->toBe('2026-09-09')
        ->and($audit->new_data_ordem)->toBe('2026-08-10')
        ->and($audit->context['data_ordem_changed'])->toBeTrue()
        ->and($audit->context['changed_fields'])->toContain('data_ordem');

    $response = $this->get(route('os.html', ['id' => $ordemServico->id]));

    $response->assertOk()
        ->assertSee('10 DE AGOSTO DE 2026');

    $pdfAudit = OrdemServicoAudit::query()
        ->where('ordem_servico_id', $ordemServico->id)
        ->where('event', 'pdf_opened')
        ->latest('id')
        ->firstOrFail();

    expect($pdfAudit->new_data_ordem)->toBe('2026-08-10')
        ->and($pdfAudit->context['requested_id'])->toBe((string) $ordemServico->id)
        ->and($pdfAudit->context['formatted_data_ordem'])
        ->toContain('10 DE AGOSTO DE 2026');
});
