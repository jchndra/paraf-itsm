<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_request_id')->constrained('change_requests')->restrictOnDelete();
            $table->foreignId('release_team_member_id')->constrained('release_team_members')->restrictOnDelete();
            $table->foreignId('assigned_by_id')->constrained('users')->restrictOnDelete();
            $table->text('instruction');
            $table->dateTime('scheduled_start')->index();
            $table->dateTime('scheduled_end')->index();
            $table->enum('execution_status', ['pending', 'in_progress', 'successful', 'rolled_back'])->default('pending')->index();
            $table->dateTime('executed_at')->nullable();
            $table->text('execution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_tasks');
    }
};
