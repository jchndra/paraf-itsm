<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('rfc_number', 30)->unique()->index();
            $table->string('title')->index();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->foreignId('submitted_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->boolean('is_physical_letter')->default(false)->index();
            $table->string('physical_letter_number', 100)->nullable();
            $table->text('reason_for_change');
            $table->text('impact_description');
            $table->string('status', 30)->default('draft')->index();
            $table->enum('change_type', ['standard', 'normal', 'emergency'])->nullable()->index();
            $table->boolean('emergency_unconfirmed')->default(false)->index();
            $table->text('rejection_note')->nullable();
            $table->dateTime('target_implementation_date')->nullable()->index();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
