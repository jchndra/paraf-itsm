<?php

namespace App\Enums;

enum RfcStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case RevisionRequired = 'revision_required';
    case Complete = 'complete';
    case Assessed = 'assessed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RevisionRequested = 'revision_requested';
    case Scheduled = 'scheduled';
    case Executing = 'executing';
    case Successful = 'successful';
    case RolledBack = 'rolled_back';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Diajukan',
            self::RevisionRequired => 'Perlu Perbaikan (Admin)',
            self::Complete => 'Lengkap',
            self::Assessed => 'Sudah Dinilai',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::RevisionRequested => 'Perlu Revisi (CAB)',
            self::Scheduled => 'Dijadwalkan',
            self::Executing => 'Sedang Dilaksanakan',
            self::Successful => 'Pelaksanaan Berhasil',
            self::RolledBack => 'Pelaksanaan Rollback',
            self::Closed => 'Selesai & Ditutup',
        };
    }
}
