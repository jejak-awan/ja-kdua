<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Base API Controller
 * 
 * Provides standardized response methods for all API controllers.
 * All API controllers should extend this class to ensure consistent
 * response format across the application.
 * 
 * @package App\Http\Controllers\Api\V1
 */
class BaseApiController extends Controller
{
    /**
     * Success response
     * 
     * Returns a standardized success response with optional data.
     * 
     * @param mixed $data Response data (optional)
     * @param string $message Success message
     * @param int $status HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Error response
     * 
     * Returns standardized error response with error code and trace ID.
     * Automatically logs error with context for debugging.
     * 
     * @param string $message Error message
     * @param int $status HTTP status code (default: 400)
     * @param array $errors Validation errors (optional)
     * @param string $code Error code (optional, default: 'ERROR')
     * @param array $context Additional context for logging (optional)
     * @return JsonResponse
     */
    protected function error(
        string $message = 'Error',
        int $status = 400,
        array $errors = [],
        string $code = 'ERROR',
        array $context = []
    ): JsonResponse {
        $traceId = uniqid('err_', true);
        
        // Log error with context
        Log::warning('API Error', array_merge([
            'message' => $message,
            'status' => $status,
            'code' => $code,
            'trace_id' => $traceId,
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ], $context));
        
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $code,
            'trace_id' => $traceId,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $status);
    }

    /**
     * Validation error response
     * 
     * Returns a standardized validation error response (422 status).
     * Should be used when ValidationException is caught.
     * 
     * @param array $errors Validation errors from ValidationException
     * @param string|null $message Custom error message (optional)
     * @return JsonResponse
     */
    protected function validationError(array $errors, string $message = null): JsonResponse
    {
        return $this->error(
            $message ?? 'Validation failed. Please check your input.',
            422,
            $errors,
            'VALIDATION_ERROR'
        );
    }

    /**
     * Not found error response
     * 
     * Returns a standardized 404 not found response.
     * 
     * @param string $resource Resource name (default: 'Resource')
     * @return JsonResponse
     */
    protected function notFound(string $resource = 'Resource'): JsonResponse
    {
        return $this->error(
            "{$resource} not found.",
            404,
            [],
            'NOT_FOUND'
        );
    }

    /**
     * Unauthorized error response
     * 
     * Returns a standardized 401 unauthorized response.
     * 
     * @param string $message Error message (optional)
     * @return JsonResponse
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error(
            $message,
            401,
            [],
            'UNAUTHORIZED'
        );
    }

    /**
     * Forbidden error response
     * 
     * Returns a standardized 403 forbidden response.
     * 
     * @param string $message Error message (optional)
     * @return JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error(
            $message,
            403,
            [],
            'FORBIDDEN'
        );
    }

    /**
     * Paginated response
     * 
     * Returns a standardized paginated response.
     * 
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string $message Success message
     * @return JsonResponse
     */
    protected function paginated($paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->success([
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $message);
    }

    /**
     * Handle exception with standardized error response
     * 
     * Helper method to catch and handle exceptions consistently.
     * Automatically handles ValidationException and other exceptions.
     * 
     * @param \Exception $e Exception to handle
     * @param string $defaultMessage Default error message
     * @param string $logContext Additional context for logging
     * @return JsonResponse
     */
    protected function handleException(\Exception $e, string $defaultMessage = 'An error occurred', string $logContext = ''): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->validationError($e->errors());
        }
        
        Log::error($logContext . ' API error: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        
        return $this->error(
            $defaultMessage,
            500,
            [],
            'SERVER_ERROR'
        );
    }
}

