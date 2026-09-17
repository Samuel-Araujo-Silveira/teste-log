<?php

namespace App\Http\Middleware;

use App\Constants\LogConstant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $correlationId = $this->resolveCorrelationId($request);

        $request->merge([
            'correlation_id' => $correlationId,
        ]);

        Log::withContext([
            'correlation_id' => $correlationId,
        ]);

        $response = $next($request);

        $response->headers->set(LogConstant::CORRELATION_ID_HEADER, $correlationId);

        return $response;
    }

    private function resolveCorrelationId(Request $request)
    {
        $incoming = $request->header(LogConstant::CORRELATION_ID_HEADER);

        if (is_string($incoming) && Str::isUuid($incoming)) {
            return $incoming;
        }

        return (string) Str::uuid();
    }
}
