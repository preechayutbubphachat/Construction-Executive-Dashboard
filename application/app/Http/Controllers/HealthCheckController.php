<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * Liveness probe: verifies that the web worker/PHP process is responsive.
     */
    public function liveness(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'ced-app',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Readiness probe: verifies that the application is ready to accept traffic
     * by validating database connectivity and filesystem writeability.
     */
    public function readiness(): JsonResponse
    {
        $dbHealthy = false;
        $dbMessage = 'connected';

        try {
            DB::connection()->getPdo();
            $dbHealthy = true;
        } catch (\Throwable $e) {
            $dbHealthy = false;
            $dbMessage = $e->getMessage();
        }

        $storagePath = storage_path();
        $storageHealthy = is_writable($storagePath);

        $isReady = $dbHealthy && $storageHealthy;

        return response()->json([
            'status' => $isReady ? 'ready' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'components' => [
                'database' => [
                    'status' => $dbHealthy ? 'ok' : 'failed',
                    'message' => $dbHealthy ? 'connected' : $dbMessage,
                ],
                'storage' => [
                    'status' => $storageHealthy ? 'ok' : 'failed',
                    'path' => $storagePath,
                ],
            ],
        ], $isReady ? 200 : 503);
    }
}
