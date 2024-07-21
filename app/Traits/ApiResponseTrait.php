<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponseTrait
{
    protected array $data;
    protected mixed $responseMessage;

    /**
     * Return generic json response with the given data.
     *
     * @param array $data
     * @param mixed $responseMessage
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    protected function makeSuccessResponse(array $data, $responseMessage, int $statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'data'=> $data,
            'message' => $responseMessage,
        ], $statusCode);
    }

    /**
     * Return generic json response with the given data.
     * @param mixed $responseMessage
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    protected function makeErrorResponse(
        $responseMessage,
        string $exceptionCode = '',
        int $statusCode = Response::HTTP_BAD_REQUEST
    ) {
        return response()->json([
            'success' => false,
            'message' => $responseMessage,
            'exception' => $exceptionCode,
        ], $statusCode);
    }
}
