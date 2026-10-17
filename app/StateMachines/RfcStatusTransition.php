<?php

namespace App\StateMachines;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use InvalidArgumentException;

class RfcStatusTransition
{
    /**
     * Memeriksa apakah transisi status dari status saat ini ke status target diizinkan.
     */
    public static function canTransition(ChangeRequest $rfc, RfcStatus $targetStatus, User $user): bool
    {
        $currentStatus = RfcStatus::from($rfc->status);

        // Aturan Pemisahan Tugas: Pemohon tidak boleh memutuskan RFC miliknya sendiri
        if (in_array($targetStatus, [RfcStatus::Approved, RfcStatus::Rejected]) && $rfc->submitted_by_id === $user->id) {
            return false;
        }

        return match ($currentStatus) {
            RfcStatus::Draft => in_array($targetStatus, [RfcStatus::Submitted]),

            RfcStatus::Submitted => in_array($targetStatus, [RfcStatus::Complete, RfcStatus::RevisionRequired])
                && $user->can('rfc.review_completeness'),

            RfcStatus::RevisionRequired => in_array($targetStatus, [RfcStatus::Submitted])
                && ($user->id === $rfc->submitted_by_id || $user->can('rfc.create')),

            RfcStatus::Complete => in_array($targetStatus, [RfcStatus::Assessed])
                && $user->can('rfc.assess_risk')
                && $rfc->riskAssessment()->exists(),

            RfcStatus::Assessed => in_array($targetStatus, [RfcStatus::Approved, RfcStatus::Rejected, RfcStatus::RevisionRequested])
                && $user->can('rfc.record_decision')
                && $rfc->decisions()->exists()
                && $rfc->authorizationDocuments()->exists(),

            RfcStatus::Approved => in_array($targetStatus, [RfcStatus::Scheduled])
                && $user->can('rfc.schedule'),

            RfcStatus::Scheduled => in_array($targetStatus, [RfcStatus::Executing])
                && $user->can('rfc.assign_release')
                && $rfc->releaseTasks()->exists(),

            RfcStatus::Executing => in_array($targetStatus, [RfcStatus::Successful, RfcStatus::RolledBack])
                && $user->can('rfc.record_execution')
                && static::validateEvidenceRequirements($rfc, $targetStatus),

            RfcStatus::Successful, RfcStatus::RolledBack => in_array($targetStatus, [RfcStatus::Closed])
                && $user->can('rfc.close')
                // Emergency change freeze check jika >24 jam belum ada konfirmasi
                && !($rfc->change_type === 'emergency' && $rfc->emergency_unconfirmed),

            default => false,
        };
    }

    /**
     * Eksekusi perubahan status jika validasi berhasil.
     */
    public static function transitionTo(ChangeRequest $rfc, RfcStatus $targetStatus, User $user): void
    {
        if (! static::canTransition($rfc, $targetStatus, $user)) {
            throw new InvalidArgumentException("Transisi status RFC dari [{$rfc->status}] ke [{$targetStatus->value}] tidak diizinkan atau syarat belum terpenuhi.");
        }

        $rfc->status = $targetStatus->value;

        if ($targetStatus === RfcStatus::Closed) {
            $rfc->closed_at = now();
        }

        $rfc->save();
    }

    /**
     * Validasi keberadaan bukti uji/rollback sesuai aturan bisnis 3
     */
    private static function validateEvidenceRequirements(ChangeRequest $rfc, RfcStatus $targetStatus): bool
    {
        if ($targetStatus === RfcStatus::Successful) {
            return $rfc->evidences()->where('evidence_type', 'test_result')->exists();
        }

        if ($targetStatus === RfcStatus::RolledBack) {
            return $rfc->evidences()->where('evidence_type', 'rollback_proof')->exists();
        }

        return false;
    }
}
