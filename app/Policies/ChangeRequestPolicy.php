<?php

namespace App\Policies;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;

class ChangeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['rfc.view_own', 'rfc.view_all']);
    }

    public function view(User $user, ChangeRequest $rfc): bool
    {
        if ($user->hasPermissionTo('rfc.view_all')) {
            return true;
        }

        return $user->hasPermissionTo('rfc.view_own') && $rfc->submitted_by_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('rfc.create');
    }

    public function update(User $user, ChangeRequest $rfc): bool
    {
        // Pemohon hanya boleh mengedit saat draft atau perlu revisi.
        if (in_array(RfcStatus::fromValue($rfc->status), [RfcStatus::DRAFT, RfcStatus::NEEDS_REVISION], true)) {
            return $rfc->submitted_by_id === $user->id;
        }

        return false;
    }

    public function reviewCompleteness(User $user): bool
    {
        return $user->hasPermissionTo('rfc.review_completeness');
    }

    public function assessRisk(User $user, ChangeRequest $rfc): bool
    {
        // Aturan Segregation of Duties (SoD): Penilai risiko tidak boleh yang mengajukan RFC
        if ($rfc->submitted_by_id === $user->id && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $user->hasPermissionTo('rfc.assess_risk');
    }

    public function recordDecision(User $user): bool
    {
        return $user->hasPermissionTo('rfc.record_decision');
    }

    public function close(User $user): bool
    {
        return $user->hasPermissionTo('rfc.close');
    }
}
