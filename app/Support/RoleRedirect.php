<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

class RoleRedirect
{
    /**
     * Resolve the post-authentication destination path for the given user.
     */
    public static function pathFor(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin, UserRole::Manager => route('admin.dashboard', absolute: false),
            UserRole::Customer => route('quotes.index', absolute: false),
        };
    }
}
