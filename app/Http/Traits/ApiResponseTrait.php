<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Response Trait
 *
 * Provides standardized response format for all API endpoints
 * Ensures consistent error handling and response structure
 */
trait ApiResponseTrait
{
    /**
     * Success response
     *
     * @param  mixed  $data
     */
    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $response['meta'] = [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ];

        return response()->json($response, $status);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        string $code = 'ERROR',
        int $status = 400,
        array $details = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'status' => $status,
            ],
        ];

        if (! empty($details)) {
            $response['error']['details'] = $details;
        }

        $response['meta'] = [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ];

        // Log error for monitoring
        Log::error('API Error', [
            'code' => $code,
            'message' => $message,
            'status' => $status,
            'details' => $details,
        ]);

        return response()->json($response, $status);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            'VALIDATION_ERROR',
            422,
            $errors
        );
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            "{$resource} not found",
            'NOT_FOUND',
            404
        );
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(): JsonResponse
    {
        return $this->errorResponse(
            'Unauthorized access',
            'UNAUTHORIZED',
            401
        );
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(): JsonResponse
    {
        return $this->errorResponse(
            'Access forbidden',
            'FORBIDDEN',
            403
        );
    }

    /**
     * Paginated response
     *
     * @param  mixed  $paginator
     */
    protected function paginatedResponse($paginator, string $message = 'Success'): JsonResponse
    {
        return $this->successResponse([
            'items' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $message);
    }

    /**
     * Created response
     *
     * @param  mixed  $data
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * No content response
     *
     * @return \Illuminate\Http\Response
     */
    protected function noContentResponse()
    {
        return response()->noContent();
    }
}
