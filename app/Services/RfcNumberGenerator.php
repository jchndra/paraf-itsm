<?php

namespace App\Services;

use App\Models\ChangeRequest;
use Illuminate\Support\Facades\DB;

class RfcNumberGenerator
{
    public static function generate(): string
    {
        $year = date('Y');
        $prefix = "RFC-{$year}-";

        return DB::transaction(function () use ($prefix) {
            $latestRfc = ChangeRequest::withTrashed()
                ->where('rfc_number', 'LIKE', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if (! $latestRfc) {
                return "{$prefix}0001";
            }

            $lastNumber = (int) substr($latestRfc->rfc_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            return "{$prefix}{$nextNumber}";
        });
    }
}
