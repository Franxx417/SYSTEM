<?php

namespace App\Http\Controllers\Api;

use App\Domain\Health\HealthCheckService;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

/**
 * Health Check Controller
 *
 * Provides system health monitoring endpoints
 * Used for service monitoring and alerting
 */
class HealthCheckController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly HealthCheckService $health
    ) {}

    /**
     * Basic health check
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successResponse(
            $this->health->basic(),
            'Service is healthy'
        );
    }

    /**
     * Detailed health check
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailed()
    {
        $result = $this->health->detailed();

        return response()->json(
            $result['response'],
            $result['http_status']
        );
    }
}
