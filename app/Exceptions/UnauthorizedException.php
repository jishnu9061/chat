<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnauthorizedException extends Exception
{
    use ApiResponseTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(Request $request): JsonResponse
    {
        return $this->makeErrorResponse(
            'Unauthorized',
            'UnauthorizedException',
            Response::HTTP_UNAUTHORIZED
        );
    }
}
