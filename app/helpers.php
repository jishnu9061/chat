<?php

declare(strict_types=1);

use App\Exceptions\UserNotFoundException;
use App\Models\User;

function getUser(): User
{
    $user = auth()->user();

    if (! $user) {
        throw new UserNotFoundException();
    }

    return $user;
}
