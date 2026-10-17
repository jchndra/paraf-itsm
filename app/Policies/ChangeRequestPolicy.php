<?php

namespace App\Policies;

use App\Models\ChangeRequest;
use App\Models\User;

class ChangeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('rfc.view_all') || $user->can('rfc.view_own');
    }

    public function view(User $user, ChangeRequest $changeRequest): bool
    {
        if ($user->can('rfc.view_all')) {
            return true;
        }

        return $user->can('rfc.view_own') && $changeRequest->submitted_by_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('rfc.create');
    }

    public function update(User $user, ChangeRequest $changeRequest): bool
    {
        // Hanya pemohon yang bisa mengedit RFC jika dalam status draft atau perbaikan
        if (in_array($changeRequest->status, ['draft', 'revision_required'])) {
            return $user->id === $changeRequest->submitted_by_id || $user->can('rfc.create');
        }

        return false;
    }

    /**
     * Pemisahan Tugas: Pengaju tidak boleh memutuskan RFC miliknya sendiri
     */
    public function decide(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $user->can('rfc.record_decision')) {
            return false;
        }

        // Aturan Pemisahan Tugas Utama
        return $changeRequest->submitted_by_id !== $user->id;
    }
}
