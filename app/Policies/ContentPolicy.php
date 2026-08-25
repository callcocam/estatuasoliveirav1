<?php

namespace App\Policies;

use App\Models\User;

/**
 * Base policy for content resources managed by Admin and Manager roles.
 *
 * Model arguments are intentionally omitted: every check is role-based,
 * which also allows class-level checks like `can('update', Product::class)`.
 * Restore reuses the `delete` ability (no dedicated method).
 */
abstract class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageContent();
    }

    public function view(User $user): bool
    {
        return $user->canManageContent();
    }

    public function create(User $user): bool
    {
        return $user->canManageContent();
    }

    public function update(User $user): bool
    {
        return $user->canManageContent();
    }

    public function delete(User $user): bool
    {
        return $user->canManageContent();
    }
}
