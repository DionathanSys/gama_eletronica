<?php

namespace App\Http\Controllers\Api\Migracao;

use App\Http\Controllers\Controller;
use App\Models\Contato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContatosMigrationController extends Controller
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
            'parceiro_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $limit = $validated['limit'] ?? 500;
        $afterId = $validated['after_id'] ?? 0;
        $updatedFrom = $validated['updated_from'] ?? null;
        $parceiroId = $validated['parceiro_id'] ?? null;

        $query = Contato::query()
            ->select([
                'id',
                'parceiro_id',
                'nome_contato',
                'email',
                'telefone_fixo',
                'telefone_cel',
                'envio_ordem',
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

        $records = $query->get();
        $items = $records->take($limit)->values();
        $hasMore = $records->count() > $limit;
        $nextAfterId = $hasMore ? $items->last()?->id : null;

        return response()->json([
            'data' => $items->map(function (Contato $contato) {
                return [
                    'legacy_id' => $contato->id,
                    'legacy_parceiro_id' => $contato->parceiro_id,
                    'nome_contato' => $contato->nome_contato,
                    'email' => $contato->email,
                    'telefone_fixo' => $contato->telefone_fixo,
                    'telefone_cel' => $contato->telefone_cel,
                    'envio_ordem' => (bool) $contato->envio_ordem,
                    'created_at' => optional($contato->created_at)?->toISOString(),
                    'updated_at' => optional($contato->updated_at)?->toISOString(),
                ];
            })->all(),
            'meta' => [
                'resource' => 'contatos',
                'count' => $items->count(),
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_after_id' => $nextAfterId,
                'filters' => [
                    'after_id' => $afterId,
                    'updated_from' => $updatedFrom,
                    'parceiro_id' => $parceiroId,
                ],
            ],
        ]);
    }
}
