<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class AuthController extends ApiBaseController
{
    public function __construct(
        protected readonly User $user
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            throw new InvalidCredentialsException();
        }

        /** @var User $user */
        $user = auth()->user();

        $token = $user->createToken('auth_token');

        return $this->makeSuccessResponse(
            [
                'user' => $user,
                'access_token' => $token->accessToken,
                'expires_in' => $token->token->expires_at->timestamp,
            ],
            'Login successful',
        );
    }

}
