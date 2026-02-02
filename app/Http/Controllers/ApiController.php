<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class ApiController extends Controller
{
    protected function apiResponse(callable $callback): JsonResponse
    {

        if (count(request()->query()) > 30) {
            Log::warning('Query param limit exceeded', [
                'count' => count(request()->query()),
                'ip' => request()->ip(),
            ]);

            return response()->json([
                'message' => 'Internal Server Error',
            ], 500);
        }

        try {
            return response()->json($callback());
        } catch (Throwable $th) {
            Log::error('API error', [
                'controller' => static::class,
                'exception' => $th,
            ]);

            if (config('app.debug')) {
                $message = implode("\n", [
                    'API error',
                    $th->getMessage(),
                    'in ' . $th->getFile(),
                    'on line ' . $th->getLine(),
                ]);
            } else {
                $message = 'API error';
            }

            return response()->json(
                ['errorMsg' => $message],
                500
            );
        }
    }
}
