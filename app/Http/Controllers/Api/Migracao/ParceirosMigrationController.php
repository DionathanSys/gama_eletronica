<?php

namespace App\Http\Controllers\Api\Migracao;

use App\Http\Controllers\Controller;
use App\Models\Parceiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParceirosMigrationController extends Controller
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
        ]);

        $limit = $validated['limit'] ?? 500;
        $afterId = $validated['after_id'] ?? 0;
        $updatedFrom = $validated['updated_from'] ?? null;
        $includeDeleted = (bool) ($validated['include_deleted'] ?? false);

        $query = Parceiro::query()
            ->select([
                'id',
                'nome',
                'tipo_vinculo',
                'tipo_documento',
                'nro_documento',
                'ativo',
                'inscricao_estadual',
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

        $records = $query->get();
        $hasMore = $records->count() > $limit;
        $items = $records->take($limit)->values();
        $nextAfterId = $hasMore ? $items->last()?->id : null;

        return response()->json([
            'data' => $items->map(function (Parceiro $parceiro) {
                return [
                    'legacy_id' => $parceiro->id,
                    'nome' => $parceiro->nome,
                    'tipo_vinculo' => $parceiro->tipo_vinculo,
                    'tipo_documento' => $parceiro->tipo_documento,
                    'nro_documento' => (string) $parceiro->nro_documento,
                    'ativo' => (bool) $parceiro->ativo,
                    'inscricao_estadual' => $parceiro->inscricao_estadual,
                    'created_at' => optional($parceiro->created_at)?->toISOString(),
                    'updated_at' => optional($parceiro->updated_at)?->toISOString(),
                    'deleted_at' => optional($parceiro->deleted_at)?->toISOString(),
                ];
            })->all(),
            'meta' => [
                'resource' => 'parceiros',
                'count' => $items->count(),
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_after_id' => $nextAfterId,
                'filters' => [
                    'after_id' => $afterId,
                    'updated_from' => $updatedFrom,
                    'include_deleted' => $includeDeleted,
                ],
            ],
        ]);
    }
}
