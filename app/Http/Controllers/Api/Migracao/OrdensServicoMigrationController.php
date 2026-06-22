<?php

namespace App\Http\Controllers\Api\Migracao;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdensServicoMigrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $configuredKey = (string) config('app.migration_api_key', env('MIGRATION_API_KEY', ''));
        $providedKey = (string) ($request->query('key') ?? $request->header('X-Migration-Key') ?? '');

        if ($configuredKey !== '' && !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'updated_from' => ['nullable', 'date'],
            'parceiro_id' => ['nullable', 'integer', 'min:1'],
            'equipamento_id' => ['nullable', 'integer', 'min:1'],
            'fatura_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string'],
        ]);

        $limit = $validated['limit'] ?? 200;
        $afterId = $validated['after_id'] ?? 0;
        $updatedFrom = $validated['updated_from'] ?? null;
        $parceiroId = $validated['parceiro_id'] ?? null;
        $equipamentoId = $validated['equipamento_id'] ?? null;
        $faturaId = $validated['fatura_id'] ?? null;
        $status = $validated['status'] ?? null;

        $query = OrdemServico::query()
            ->with([
                'itens:id,ordem_servico_id,servico_id,quantidade,valor_unitario,valor_total,desconto,observacao,garantia',
                'itens.servico:id,nome,descricao,valor_unitario,ativo',
            ])
            ->select([
                'id',
                'parceiro_id',
                'equipamento_id',
                'placa',
                'fatura_id',
                'data_ordem',
                'data_encerrado',
                'valor_total',
                'desconto',
                'prioridade',
                'tipo_manutencao',
                'status',
                'status_processo',
                'relato_cliente',
                'itens_recebidos',
                'path_pdf',
                'img_equipamento',
                'nota_entrada_id',
                'nota_retorno_id',
                'observacao_geral',
                'observacao_interna',
                'created_at',
                'updated_at',
            ])
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit + 1);

        if ($updatedFrom) {
            $query->where(function ($builder) use ($updatedFrom) {
                $builder->where('updated_at', '>=', $updatedFrom)
                    ->orWhere('created_at', '>=', $updatedFrom);
            });
        }

        if ($parceiroId) {
            $query->where('parceiro_id', $parceiroId);
        }

        if ($equipamentoId) {
            $query->where('equipamento_id', $equipamentoId);
        }

        if ($faturaId) {
            $query->where('fatura_id', $faturaId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $records = $query->get();
        $items = $records->take($limit)->values();
        $hasMore = $records->count() > $limit;
        $nextAfterId = $hasMore ? $items->last()?->id : null;

        return response()->json([
            'data' => $items->map(function (OrdemServico $ordemServico) {
                return [
                    'legacy_id' => $ordemServico->id,
                    'legacy_parceiro_id' => $ordemServico->parceiro_id,
                    'legacy_equipamento_id' => $ordemServico->equipamento_id,
                    'legacy_fatura_id' => $ordemServico->fatura_id,
                    'placa' => $ordemServico->placa,
                    'data_ordem' => $ordemServico->data_ordem,
                    'data_encerrado' => $ordemServico->data_encerrado,
                    'valor_total' => (float) $ordemServico->valor_total,
                    'desconto' => (float) $ordemServico->desconto,
                    'prioridade' => $ordemServico->prioridade,
                    'tipo_manutencao' => $ordemServico->tipo_manutencao,
                    'status' => $ordemServico->status,
                    'status_processo' => $ordemServico->status_processo,
                    'relato_cliente' => $ordemServico->relato_cliente,
                    'itens_recebidos' => $ordemServico->itens_recebidos,
                    'path_pdf' => $ordemServico->path_pdf,
                    'img_equipamento' => $ordemServico->img_equipamento,
                    'nota_entrada_id' => $ordemServico->nota_entrada_id,
                    'nota_retorno_id' => $ordemServico->nota_retorno_id,
                    'observacao_geral' => $ordemServico->observacao_geral,
                    'observacao_interna' => $ordemServico->observacao_interna,
                    'created_at' => optional($ordemServico->created_at)?->toISOString(),
                    'updated_at' => optional($ordemServico->updated_at)?->toISOString(),
                    'itens' => $ordemServico->itens->map(function ($item) {
                        return [
                            'legacy_id' => $item->id,
                            'legacy_ordem_servico_id' => $item->ordem_servico_id,
                            'legacy_servico_id' => $item->servico_id,
                            'servico' => $item->servico ? [
                                'legacy_id' => $item->servico->id,
                                'nome' => $item->servico->nome,
                                'descricao' => $item->servico->descricao,
                                'valor_unitario' => (float) $item->servico->valor_unitario,
                                'ativo' => (bool) $item->servico->ativo,
                            ] : null,
                            'quantidade' => (float) $item->quantidade,
                            'valor_unitario' => (float) $item->valor_unitario,
                            'valor_total' => (float) $item->valor_total,
                            'desconto' => (float) $item->desconto,
                            'observacao' => $item->observacao,
                            'garantia' => is_null($item->garantia) ? null : (bool) $item->garantia,
                        ];
                    })->values()->all(),
                ];
            })->all(),
            'meta' => [
                'resource' => 'ordens_servico',
                'count' => $items->count(),
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_after_id' => $nextAfterId,
                'filters' => [
                    'after_id' => $afterId,
                    'updated_from' => $updatedFrom,
                    'parceiro_id' => $parceiroId,
                    'equipamento_id' => $equipamentoId,
                    'fatura_id' => $faturaId,
                    'status' => $status,
                ],
            ],
        ]);
    }
}
