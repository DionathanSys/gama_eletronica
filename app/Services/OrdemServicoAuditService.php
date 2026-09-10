<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\OrdemServicoAudit;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class OrdemServicoAuditService
{
    public static function record(
        string $event,
        ?OrdemServico $ordemServico = null,
        ?string $source = null,
        mixed $oldDataOrdem = null,
        mixed $newDataOrdem = null,
        array $context = [],
    ): ?OrdemServicoAudit {
        try {
            $requestContext = self::requestContext();

            return OrdemServicoAudit::create([
                'ordem_servico_id' => $ordemServico?->getKey(),
                'user_id' => Auth::id(),
                'event' => $event,
                'source' => $source,
                'request_id' => $requestContext['request_id'],
                'old_data_ordem' => self::dateString($oldDataOrdem),
                'new_data_ordem' => self::dateString($newDataOrdem),
                'context' => $context ?: null,
                'ip_address' => $requestContext['ip_address'],
                'user_agent' => $requestContext['user_agent'],
                'url' => $requestContext['url'],
            ]);
        } catch (Throwable $exception) {
            // Auditoria não deve impedir a operação original, mas a falha precisa ser rastreável.
            Log::error('Falha ao registrar auditoria da ordem de serviço', [
                'event' => $event,
                'ordem_servico_id' => $ordemServico?->getKey(),
                'source' => $source,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private static function dateString(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return $date->toDateString();
        }

        return (string) $date;
    }

    /**
     * Correlates model, form and PDF records generated in the same request.
     *
     * @return array{request_id: string, ip_address: string|null, user_agent: string|null, url: string|null}
     */
    private static function requestContext(): array
    {
        if (! app()->bound('request')) {
            return [
                'request_id' => (string) Str::uuid(),
                'ip_address' => null,
                'user_agent' => null,
                'url' => null,
            ];
        }

        $request = request();
        $requestId = $request->attributes->get('ordem_servico_audit_request_id');

        if (! $requestId) {
            $requestId = $request->header('X-Request-ID') ?: (string) Str::uuid();
            $request->attributes->set('ordem_servico_audit_request_id', $requestId);
        }

        return [
            'request_id' => (string) $requestId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ];
    }
}
