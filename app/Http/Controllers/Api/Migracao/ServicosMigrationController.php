<?php

namespace App\Http\Controllers\Api\Migracao;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicosMigrationController extends Controller
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
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'updated_from' => ['nullable', 'date'],
            'include_deleted' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $limit = $validated['limit'] ?? 500;
        $afterId = $validated['after_id'] ?? 0;
        $updatedFrom = $validated['updated_from'] ?? null;
        $includeDeleted = (bool) ($validated['include_deleted'] ?? false);
        $ativo = $validated['ativo'] ?? null;

        $query = Servico::query()
            ->select([
                'id',
                'nome',
                'descricao',
                'valor_unitario',
                'ativo',
                'imposto_servico_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit($limit + 1);

        if ($includeDeleted) {
            $query->withTrashed();
        }

        if ($updatedFrom) {
            $query->where(function ($builder) use ($updatedFrom, $includeDeleted) {
                $builder->where('updated_at', '>=', $updatedFrom)
                    ->orWhere('created_at', '>=', $updatedFrom);

                if ($includeDeleted) {
                    $builder->orWhere('deleted_at', '>=', $updatedFrom);
                }
            });
        }

        if ($ativo !== null) {
            $query->where('ativo', (bool) $ativo);
        }

        $records = $query->get();
        $items = $records->take($limit)->values();
        $hasMore = $records->count() > $limit;
        $nextAfterId = $hasMore ? $items->last()?->id : null;

        return response()->json([
            'data' => $items->map(function (Servico $servico) {
                return [
                    'legacy_id' => $servico->id,
                    'nome' => $servico->nome,
                    'descricao' => $servico->descricao,
                    'valor_unitario' => (float) $servico->valor_unitario,
                    'ativo' => (bool) $servico->ativo,
                    'imposto_servico_id' => $servico->imposto_servico_id,
                    'created_at' => optional($servico->created_at)?->toISOString(),
                    'updated_at' => optional($servico->updated_at)?->toISOString(),
                    'deleted_at' => optional($servico->deleted_at)?->toISOString(),
                ];
            })->all(),
            'meta' => [
                'resource' => 'servicos',
                'count' => $items->count(),
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_after_id' => $nextAfterId,
                'filters' => [
                    'after_id' => $afterId,
                    'updated_from' => $updatedFrom,
                    'include_deleted' => $includeDeleted,
                    'ativo' => $ativo,
                ],
            ],
        ]);
    }
}
