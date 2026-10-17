<?php

namespace App\StateMachines;

use App\Enums\RfcStatus;
use App\Models\ChangeRequest;
use App\Models\User;
use InvalidArgumentException;

class RfcStatusTransition
{
    /**
     * Peta transisi status yang diizinkan (Status Awal => [Daftar Status Tujuan])
     */
    protected static array $allowedTransitions = [
        RfcStatus::Draft->value => [
            RfcStatus::Submitted->value,
        ],
        RfcStatus::Submitted->value => [
            RfcStatus::Complete->value,
            RfcStatus::RevisionRequired->value,
            RfcStatus::Rejected->value,
        ],
        RfcStatus::RevisionRequired->value => [
            RfcStatus::Submitted->value,
        ],
        RfcStatus::Complete->value => [
            RfcStatus::Assessed->value,
            RfcStatus::Rejected->value,
        ],
        RfcStatus::Assessed->value => [
            RfcStatus::Approved->value,
            RfcStatus::Rejected->value,
            RfcStatus::RevisionRequested->value,
        ],
        RfcStatus::RevisionRequested->value => [
            RfcStatus::Assessed->value,
            RfcStatus::Submitted->value,
        ],
        RfcStatus::Approved->value => [
            RfcStatus::Scheduled->value,
            RfcStatus::Executing->value,
            RfcStatus::Successful->value,
            RfcStatus::RolledBack->value,
        ],
        RfcStatus::Scheduled->value => [
            RfcStatus::Executing->value,
        ],
        RfcStatus::Executing->value => [
            RfcStatus::Successful->value,
            RfcStatus::RolledBack->value,
        ],
        RfcStatus::Successful->value => [
            RfcStatus::Closed->value,
        ],
        RfcStatus::RolledBack->value => [
            RfcStatus::Closed->value,
        ],
    ];

    /**
     * Memeriksa apakah transisi status diizinkan.
     */
    public static function canTransition(ChangeRequest $rfc, RfcStatus $targetStatus, User $user): bool
    {
        // Normalisasi status saat ini ke bentuk string value
        $currentStatusValue = $rfc->status instanceof RfcStatus
            ? $rfc->status->value
            : (string) $rfc->status;

        $targetStatusValue = $targetStatus->value;

        // Cek apakah transisi terdaftar di matriks alur
        $allowed = static::$allowedTransitions[$currentStatusValue] ?? [];

        if (! in_array($targetStatusValue, $allowed, true)) {
            return false;
        }

        // Pengecualian Otorisasi / Hak Akses Bypass untuk Super Admin
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Penegakan SoD: Pemohon dilarang menyetujui/menolak RFC miliknya sendiri
        if ($rfc->submitted_by_id === $user->id && in_array($targetStatus, [RfcStatus::Approved, RfcStatus::Rejected])) {
            return false;
        }

        return true;
    }

    /**
     * Eksekusi perubahan status jika validasi berhasil.
     */
    public static function transitionTo(ChangeRequest $rfc, RfcStatus $targetStatus, User $user): void
    {
        if (! static::canTransition($rfc, $targetStatus, $user)) {
            $currentStatusValue = $rfc->status instanceof RfcStatus
                ? $rfc->status->value
                : (string) $rfc->status;

            throw new InvalidArgumentException("Transisi status RFC dari [{$currentStatusValue}] ke [{$targetStatus->value}] tidak diizinkan atau syarat belum terpenuhi.");
        }

        // Simpan status baru ke basis data
        $rfc->status = $targetStatus->value;
        $rfc->save();
    }
}
