<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $user): bool
    {
        return $user->can('users.manage');
    }

    /**
     * Pemisahan Tugas: Admin tidak boleh mengubah peran (role) dirinya sendiri
     */
    public function updateRole(User $user, User $targetUser): bool
    {
        if (! $user->can('users.manage')) {
            return false;
        }

        // Mencegah self-role update
        return $user->id !== $targetUser->id;
    }
}
