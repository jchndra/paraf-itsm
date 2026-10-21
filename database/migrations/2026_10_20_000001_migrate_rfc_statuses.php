<?php

use App\Enums\RfcStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lengkapi/analisis risiko tetap disimpan sebagai ticket_created.
        DB::table('change_requests')
            ->whereIn('status', ['complete', 'assessed'])
            ->update(['status' => RfcStatus::TICKET_CREATED->value]);

        $this->changeDecisionStatusEnum(
            ['approved', 'rejected', 'revision_required', 'returned_to_admin'],
            ['revision_required', 'needs_revision', 'revision_requested'],
            'returned_to_admin',
            ['approved', 'rejected', 'returned_to_admin']
        );
    }

    public function down(): void
    {
        // Rollback best-effort: ticket_created tidak dapat dibedakan lagi
        // apakah asalnya complete atau assessed.
        DB::table('change_requests')
            ->where('status', RfcStatus::TICKET_CREATED->value)
            ->update(['status' => 'assessed']);

        $this->changeDecisionStatusEnum(
            ['approved', 'rejected', 'returned_to_admin', 'revision_required'],
            ['returned_to_admin'],
            'revision_required',
            ['approved', 'rejected', 'revision_required']
        );
    }

    /**
     * @param  array<int, string>  $expandedValues
     * @param  array<int, string>  $valuesToReplace
     * @param  array<int, string>  $finalValues
     */
    private function changeDecisionStatusEnum(
        array $expandedValues,
        array $valuesToReplace,
        string $replacement,
        array $finalValues
    ): void {
        if (DB::getDriverName() === 'mysql') {
            $expanded = implode(',', array_map(fn (string $value) => "'{$value}'", $expandedValues));
            $final = implode(',', array_map(fn (string $value) => "'{$value}'", $finalValues));

            DB::statement("ALTER TABLE decisions MODIFY decision_status ENUM({$expanded}) NOT NULL");

            DB::table('decisions')
                ->whereIn('decision_status', $valuesToReplace)
                ->update(['decision_status' => $replacement]);

            DB::statement("ALTER TABLE decisions MODIFY decision_status ENUM({$final}) NOT NULL");

            return;
        }

        Schema::table('decisions', function (Blueprint $table) {
            $table->string('decision_status', 30)->change();
        });

        DB::table('decisions')
            ->whereIn('decision_status', $valuesToReplace)
            ->update(['decision_status' => $replacement]);

        Schema::table('decisions', function (Blueprint $table) use ($finalValues) {
            $table->enum('decision_status', $finalValues)->change();
        });
    }
};
