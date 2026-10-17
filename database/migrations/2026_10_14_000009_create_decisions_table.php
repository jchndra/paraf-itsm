<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_request_id')->constrained('change_requests')->restrictOnDelete();
            $table->foreignId('authority_id')->constrained('authorities')->restrictOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->foreignId('recorded_by_id')->constrained('users')->restrictOnDelete();

            // Snapshot Data Pejabat
            $table->string('snapshot_authority_name');
            $table->string('snapshot_authority_nip', 18)->nullable();
            $table->string('snapshot_authority_position');

            $table->enum('decision_status', ['approved', 'rejected', 'revision_required'])->index();
            $table->dateTime('decision_date')->index();
            $table->text('reason');

            // Append-only koreksi
            $table->boolean('is_replacement')->default(false)->index();
            $table->foreignId('replaces_decision_id')->nullable()->constrained('decisions')->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
