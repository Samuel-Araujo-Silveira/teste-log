<?php

namespace App\Http\Middleware;

use App\Constants\ExceptionMessageConstant;
use App\Constants\ExceptionCodeConstant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RequestContext
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->header('user-id');
        $establishmentId = $request->header('establishment-id');

        if (!$userId || !$establishmentId) {
            return response([
                'success' => false,
                'error' => [
                    'message' => ExceptionMessageConstant::UNAUTHORIZED,
                    'code' => ExceptionCodeConstant::GENERIC_ERROR,
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->merge([
            'user_id' => (int) $userId,
            'establishment_id' => (int) $establishmentId,
        ]);

        return $next($request);
    }
}
