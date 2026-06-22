<?php

namespace App\Http\Controllers\Api\Migracao;

use App\Http\Controllers\Controller;
use App\Models\Equipamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipamentosMigrationController extends Controller
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
            'parceiro_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $limit = $validated['limit'] ?? 500;
        $afterId = $validated['after_id'] ?? 0;
        $updatedFrom = $validated['updated_from'] ?? null;
        $includeDeleted = (bool) ($validated['include_deleted'] ?? false);
        $parceiroId = $validated['parceiro_id'] ?? null;

        $query = Equipamento::query()
            ->select([
                'id',
                'parceiro_id',
                'descricao',
                'nro_serie',
                'modelo',
                'marca',
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

        if ($parceiroId) {
            $query->where('parceiro_id', $parceiroId);
        }

        $records = $query->get();
        $items = $records->take($limit)->values();
        $hasMore = $records->count() > $limit;
        $nextAfterId = $hasMore ? $items->last()?->id : null;

        return response()->json([
            'data' => $items->map(function (Equipamento $equipamento) {
                return [
                    'legacy_id' => $equipamento->id,
                    'legacy_parceiro_id' => $equipamento->parceiro_id,
                    'descricao' => $equipamento->descricao,
                    'nro_serie' => $equipamento->nro_serie,
                    'modelo' => $equipamento->modelo,
                    'marca' => $equipamento->marca,
                    'created_at' => optional($equipamento->created_at)?->toISOString(),
                    'updated_at' => optional($equipamento->updated_at)?->toISOString(),
                    'deleted_at' => optional($equipamento->deleted_at)?->toISOString(),
                ];
            })->all(),
            'meta' => [
                'resource' => 'equipamentos',
                'count' => $items->count(),
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_after_id' => $nextAfterId,
                'filters' => [
                    'after_id' => $afterId,
                    'updated_from' => $updatedFrom,
                    'include_deleted' => $includeDeleted,
                    'parceiro_id' => $parceiroId,
                ],
            ],
        ]);
    }
}
