<?php

namespace App\Enums;

enum DecisionStatus: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case RETURNED_TO_ADMIN = 'returned_to_admin';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::RETURNED_TO_ADMIN => 'Dikembalikan ke Admin',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    public static function labelFor(self|string|null $value): string
    {
        if ($value instanceof self) {
            return $value->label();
        }

        return match ($value) {
            'approved' => self::APPROVED->label(),
            'rejected' => self::REJECTED->label(),
            'returned_to_admin', 'revision_required', 'needs_revision', 'revision_requested' => self::RETURNED_TO_ADMIN->label(),
            default => 'Keputusan tidak dikenal',
        };
    }
}
